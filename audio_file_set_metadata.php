#!/usr/bin/php
<?php
require './getid3/getid3.php';
require './getid3/write.php';

define('SHORT_OPTS', 'f:h');
define('LONG_OPTS', array('file:', 'help'));
define('FILE_NAME_EXCEPT', array('audio', 'video', 'official')); // Those tags will be removed from faile name (ex: [official video])

function getParam ($options, $shortOpts, $longOpts, $default = NULL) {
    foreach ($options as $key => $value) {
        if ($key === $shortOpts) {
            return $value;
        } elseif ($key === $longOpts) {
            return $value;
        }
    }
    return $default;
}

function getTagsFromFileName ($fullPath) {
    // Get the file name out of the full path
    $pathSplit = explode('/', $fullPath);
    $pathLenght = count($pathSplit) - 1;
    $fileName = $pathSplit[$pathLenght];
    // Remove file extention (.mp3)
    $fileName = preg_replace("/\.\S*$/", '', $fileName);
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
    preg_match('/(.+?)\s*-\s*(.+)/', $fileName, $matches);
    // Get data
    $title = trim($matches[2]);
    $artist = trim($matches[1]);
    $data = array(
        'title' => array($title),
        'artist' => array($artist)
    );

    return $data;
}

function setMetadata ($file, $tags) {
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

$newTags = getTagsFromFileName($fileParam);

setMetadata($fileParam, $newTags);
?>
