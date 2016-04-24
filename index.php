<?php

chdir(realpath(dirname(__FILE__)));

require_once("lib/class.image.php");

$image = new Image(100, 100);

// Draws a pixel in the middle
$image->paint(50, 50, 'black');

// Create 4 separate lines
$image->line('red')->from(10, 10)->to(90, 10);
$image->line('green')->from(90, 10)->to(90, 90);
$image->line('blue')->from(90, 90)->to(10, 90);
$image->line('yellow')->from(10, 90)->to(10, 10);

// Create a continuous line
$image->line('grey')
      ->from(15, 15)
      ->to(85, 15)
      ->to(85, 85)
      ->to(15, 85)
      ->to(15, 15);

// Randomized
$image->line('green')->from(100, 0);

// Save the image
$image->save('test.png', 'png');
