<?php

chdir(realpath(dirname(__FILE__)));

require_once("../lib/class.image.php");
require_once("../lib/functions.inc.php");

$height = arguments('height');

foreach(range(1, $height) as $row) {
    echo str_repeat(" ", $height - $row);
    echo str_repeat("#", $row);
    echo "#" . PHP_EOL;
}

// YOUR CODE STARTS HERE


// YOUR CODE ENDS HERE
echo PHP_EOL;