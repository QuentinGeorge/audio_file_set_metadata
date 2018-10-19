#!/usr/bin/php
<?php
require './getid3/getid3.php';
require './getid3/write.php';

define('SHORT_OPTS', 'f:h');
define('LONG_OPTS', array('file:', 'help'));
define('FILE_NAME_EXCEPT', array('audio', 'video', 'official')); // Those tags will be removed from file name (ex: [official video])
define('OUTPUT_DIR', 'done\\');

function getParam($options, $shortOpts, $longOpts, $default = NULL) {
    foreach ($options as $key => $value) {
        if ($key === $shortOpts) {
            return $value;
        } elseif ($key === $longOpts) {
            return $value;
        }
    }
    return $default;
}

function splitFilePath($file) {
    $fullPath = realpath($file);
    // Get the file name out of the full path
    $pathSplit = explode('/', $file);
    $pathLenght = count($pathSplit) - 1;
    $fileName = $pathSplit[$pathLenght];
    // Get the path without the file name
    $path = substr($fullPath, 0, strlen($fullPath) - strlen($fileName));
    // Get & remove file extention (.mp3)
    preg_match("/\.\S*$/", $fileName, $fileExt);
    $fileName = preg_replace("/\.\S*$/", '', $fileName);

    return array('path' => $path, 'file' => $fileName, 'ext' => $fileExt[0]);
}

function getMetaFromFileName($fileName) {
    // prepare regex for bad tags name remove
    $regex = "/[\(\[](";
    foreach (FILE_NAME_EXCEPT as $key => $value) {
        if ($key < count(FILE_NAME_EXCEPT) - 1) {
            $regex = $regex . $value . "|";
        } else {
            $regex = $regex . $value . ").+?[\)\]]/i";
        }
    }
    // Remove unexpected tags inside of the name (official video, ...)
    $fileName = preg_replace($regex, '', $fileName);
    // Explode the string at "-" but only at the first occurence
    preg_match("/(.+?)\s*-\s*(.+)/", $fileName, $matches);
    // Get data
    $title = trim($matches[2]);
    $artist = trim($matches[1]);
    $meta = array(
        'title' => array($title),
        'artist' => array($artist)
    );

    return $meta;
}

function copyAndRenameFile($file, $srcPath, $fileExt, $tags) {
    $path = $srcPath . OUTPUT_DIR;
    $newFileName = $tags['artist'][0] . ' - ' . $tags['title'][0] . $fileExt;
    // If dest directory doesn't exist, create it
    if (!is_dir($path)) {
        mkdir($path);
    }
    copy($file, $path . $newFileName);

    // Return the new file
    return $path . $newFileName;
}

function setMetadata($file, $tags) {
// This function use id3 script to write mp3 metadata (https://github.com/JamesHeinrich/getID3)
    $textEncoding = 'UTF-8';

    // Initialize getID3 engine
    $getID3 = new getID3;
    $getID3->setOption(array('encoding'=>$textEncoding));

    // Initialize getID3 tag-writing module
    $tagWriter = new getid3_writetags;
    //$tagWriter->filename = '/path/to/file.mp3';
    $tagWriter->filename = $file;

    //$tagWriter->tagformats = array('id3v1', 'id3v2.3');
    $tagWriter->tagformats = array('id3v2.3');

    // set various options (optional)
    $tagWriter->overwrite_tags = true;  // if true will erase existing tag data and write only passed data; if false will merge passed data with existing tag data (experimental)
    $tagWriter->remove_other_tags = false; // if true removes other tag formats (e.g. ID3v1, ID3v2, APE, Lyrics3, etc) that may be present in the file and only write the specified tag format(s). If false leaves any unspecified tag formats as-is.
    $tagWriter->tag_encoding = $textEncoding;
    $tagWriter->remove_other_tags = true;
    $tagWriter->tag_data = $tags;

    // write tags
    if ($tagWriter->WriteTags()) {
    	echo "Successfully wrote tags\n";
    	if (!empty($tagWriter->warnings)) {
    		echo "There were some warnings:\n" . implode("\n\n", $tagWriter->warnings);
    	}
    } else {
    	echo "Failed to write tags!\n" . implode("\n\n", $tagWriter->errors);
    }
}

$options = getopt(SHORT_OPTS, LONG_OPTS);

$fileParam = getParam($options, 'f', 'file');
$helpParam = getParam($options, 'h', 'help');

// Get file canonic path, file name & file extension
$filePathSplited = splitFilePath($fileParam);
// Get new metadata from file name
$newTags = getMetaFromFileName($filePathSplited['file']);
// Copy file into src dir, rename & get new file
$newFile = copyAndRenameFile($fileParam, $filePathSplited['path'], $filePathSplited['ext'], $newTags);
// Set metadata into new file
setMetadata($newFile, $newTags);
?>
