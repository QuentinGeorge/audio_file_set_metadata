#!/usr/bin/php
<?php
require dirname(__FILE__) . '/getid3/getid3.php';
require dirname(__FILE__) . '/getid3/write.php';

define('SHORT_OPTS', 'd:h');
define('LONG_OPTS', array('dir:', 'help'));
define('FILE_NAME_EXCEPT', array('audio', 'video', 'official', 'lyrics')); // Those tags will be removed from file name (ex: [official video])
define('OUTPUT_DIR', 'done\\');
define('OUTPUT_MSG_PAD', 30);

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

function getMP3Files($dir) {
    if (!is_dir($dir)) {
        exit('ERROR: "' . $dir . '" is not a valid directory' . "\n");
    } else {
        $mp3Files = array();
        $dirContent = scandir($dir);

        foreach ($dirContent as $value) {
            if (preg_match("/\.mp3$/", $value, $match)) {
                array_push($mp3Files, $value);
            }
        }

        return $mp3Files;
    }
}

function getMetaFromFileName($fileName) {
    // Prepare regex for bad tags name remove
    $regex = "/[\(\[](";
    foreach (FILE_NAME_EXCEPT as $key => $value) {
        if ($key < count(FILE_NAME_EXCEPT) - 1) {
            $regex = $regex . $value . "|";
        } else {
            $regex = $regex . $value . ").*?[\)\]]/i";
        }
    }
    // Remove album pist number if in begining of file name with - separator
    $fileName = preg_replace("/^[0-9]+\s*-\s*/", '', $fileName);
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

function copyAndRenameFile($file, $srcPath, $tags, $fileExt = '.mp3') {
    $path = $srcPath . "\\" . OUTPUT_DIR;
    $newFileName = $tags['artist'][0] . ' - ' . $tags['title'][0] . $fileExt;

    // If dest directory doesn't exist, create it
    if (!is_dir($path)) {
        mkdir($path);
    }
    // Copy the file into OUTPUT_DIR or return FALSE if can't
    if(!copy($file, $path . $newFileName)) {
        return FALSE;
    }
    // Return the new file
    return $path . $newFileName;
}

function setMetadata($file, $tags, $fileName) {
    $isSucess = FALSE;
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
        $isSucess = TRUE;
    	echo str_pad('Successfully wrote tags for:', OUTPUT_MSG_PAD) . $fileName . "\n";
    	if (!empty($tagWriter->warnings)) {
    		echo str_pad('There were some warnings for:', OUTPUT_MSG_PAD) . $fileName . "\n" . implode("\n\n", $tagWriter->warnings);
    	}
    } else {
    	echo str_pad('Failed to write tags for:', OUTPUT_MSG_PAD) . $fileName . "\n" . implode("\n\n", $tagWriter->errors);
    }

    return $isSucess;
}

$count = 0;
$options = getopt(SHORT_OPTS, LONG_OPTS);

$dirParam = getParam($options, 'd', 'dir');
$helpParam = getParam($options, 'h', 'help');

// Get mp3 files from a directory
$mp3Files = getMP3Files($dirParam);
// Process modifications on each files
foreach ($mp3Files as $value) {
    // Get file name without extension
    $fileName = preg_replace("/\.\S*$/", '', $value);
    // Get new metadata from file name
    $newTags = getMetaFromFileName($fileName);
    // Copy file into src dir, rename & get new file
    $newFile = copyAndRenameFile($value, $dirParam, $newTags);
    if ($newFile) {
        // Set metadata into new file
        if(setMetadata($newFile, $newTags, $value)) {
            $count++;
        }
    } else {
        echo str_pad('Unable to copy file:', OUTPUT_MSG_PAD) . $value . "\n";
    }
}
echo "\n" . $count . ' files have been successfully treated' . "\n";
?>
