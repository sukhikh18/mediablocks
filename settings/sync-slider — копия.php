<?php
defined( 'ABSPATH' ) or die();

$settings = array(
  array(
    'id' => 'xOrigin',
    'label' => 'X coordinate',
    'desc' => 'Center of the carousel (container width / 2)',
    'type' => 'number',
    ),
  array(
    'id' => 'yOrigin',
    'label' => 'Y coordinate',
    'desc' => 'Center of the carousel (container height / 10)',
    'type' => 'number',
    ),
  array(
    'id' => 'xRadius',
    'label' => 'Y coordinate',
    'desc' => 'Half the width of the carousel (container height / 6)',
    'type' => 'number',
    ),
  array(
    'id' => 'yRadius',
    'label' => 'Y coordinate',
    'desc' => 'Half the height of the carousel (container height / 6)',
    'type' => 'number',
    ),
  array(
    'id' => 'farScale',
    'label' => 'Y coordinate',
    'desc' => 'Scale of an item at its farthest point (range: 0 to 1)',
    'type' => 'number',
    'default' => '0.5'
    ),
  array(
    'id' => 'mirror',
    'label' => 'Reflections',
    'desc' => 'Reflection options none',
    'type' => 'number',
    'default' => '0.5'
    ),
  array(
    'id' => 'transforms',
    'label' => 'Transforms',
    'desc' => 'Use native CSS transforms if support for them is detected true',
    'type' => 'text',
    ),
  array(
    'id' => 'smooth',
    'label' => 'Fps Frames per second',
    'desc' => 'Use maximum effective frame rate via the requestAnimationFrame API if support is detected true. (if smooth animation is turned off)',
    'type' => 'number',
    'default' => '30'
    ),
  array(
    'id' => 'speed',
    'label' => 'Speed',
    'desc' => 'Relative speed factor of the carousel. Any positive number: 1 is slow, 4 is medium, 10 is fast.',
    'type' => 'number',
    'default' => '4'
    ),
  array(
    'id' => 'autoPlay',
    'label' => 'autoPlay',
    'desc' => 'Automatically rotate the carousel by this many items periodically (positive number is clockwise). Auto-play is not performed while the mouse hovers over the carousel container. A value of 0 means auto-play is turned off. See: autoPlayDelay 0',
    'type' => 'number',
    'default' => '0'
    ),
  array(
    'id' => 'autoPlayDelay',
    'label' => 'autoPlayDelay',
    'desc' => 'Delay, in milliseconds, between auto-play spins',
    'type' => 'number',
    'default' => '4000'
    ),
);
  

  
  
mouseWheel  Spin the carousel using the mouse wheel. Requires a "mousewheel" event, provided by this mousewheel plugin. However, see: known issues  false
bringToFront  Clicking an item will rotate it to the front  false
buttonLeft  jQuery collection of element(s) intended to spin the carousel so as to bring the item to the left of the frontmost item to the front, i.e., spin it counterclockwise, when clicked. E.g., $("#button-left") none
buttonRight jQuery collection of element(s) intended to spin the carousel so as to bring the item to the right of the frontmost item to the front, i.e., spin it clockwise, when clicked. E.g., $("#button-right")  none
itemClass Class attribute of the item elements inside the carousel container  "cloud9-item"
handle  The string handle you can use to interact with the carousel. E.g., $("#carousel").data("carousel").go(1)