<?php
defined( 'ABSPATH' ) or die();

$sizes = get_intermediate_image_sizes();
$view_sizes = array();
foreach ($sizes as $value) {
  $view_sizes[$value] = $value;
}

$settings = array(
  array(
    'id'  => 'block_template',
    'label' => 'Template',
    'desc' => 'include CSS template',
    'type' => 'select',
    'default' => 'plugin',
    'options' => array(
      ''=>'Не использовать',
      'default'=>'Standart',
      'plugin'=>'Changed',
      'custom'=>'Personal'
      )
    ),
  array(
    'id' => 'style_path',
    'label' => 'Custom path',
    'desc' => '',
    'type' => 'text',
    ),
  // $image_size = 'thumbnail';
  array(
    'id' => 'sl_width',
    'label' => 'Width',
    'desc' => '',
    'type' => 'text',
    ),
  array(
    'id' => 'sl_height',
    'label' => 'Height',
    'desc' => '',
    'type' => 'text',
    )
  );
  return $settings;