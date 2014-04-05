<?php 

// print file
echo $content;

// force download
header('Content-type: file/binary');

// set file name
header('Content-Disposition: attachment; filename="'.$filename.'"');

?>