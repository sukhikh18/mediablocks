<?php
defined( 'ABSPATH' ) or die();

$sizes = get_intermediate_image_sizes();
$view_sizes = array();
foreach ($sizes as $value) {
  $view_sizes[$value] = $value;
}

$settings = array(
  array('id' => 'lightbox',
    'label' => 'LightBox class',
    'desc' => 'Add class for lightbox links',
    'type' => 'text',
    'placeholder' => 'zoom',
     // 'default' => 'fancybox',
    ),
  array('id' => 'items_size',
    'label' => 'Image size',
    'desc' => '',
    'type' => 'select',
    'options' => $view_sizes
    ),
  array(
    'id' => 'use_template',
    'label' => 'Include Template',
    'desc' => 'include CSS template',
    'type' => 'checkbox',
    'default' => 'on',
    ),
  array('id' => 'image_captions',
    'label' => 'Show image captions',
    'desc' => '',
    'type' => 'checkbox',
    )
  );
  return $settings;