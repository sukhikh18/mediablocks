<?php

class DT_Form {
    static $clear_value = false;
    protected $inputs, $args, $is_table, $active;
    protected $hiddens = array();

    public function __construct($data = null, $active = null, $is_table = true, $args = null)
    {
        if( ! is_array($data) )
            $data = array();

        if( ! is_array($args) )
            $args = array();

        if( isset($data['id']) || isset($data['name']) )
            $data = array($data);

        $args = self::parse_defaults($args, $is_table);
        if( $args['admin_page'] )
            $data = self::admin_page_options( $data, $args['admin_page'] );

        $this->fields = $data;
        $this->args = $args;
        $this->is_table = $is_table;
        $this->active = $active;
    }

    public function render( $return=false )
    {

        $html = $this->args['form_wrap'][0];
        foreach ($this->fields as $field) {
            if ( ! isset($field['id']) && ! isset($field['name']) )
                continue;

            // &$field
            $input = self::render_input( $field, $this->active, $this->is_table );
            $html .= self::_field_template( $field, $input, $this->is_table );
        }
        $html .= $this->args['form_wrap'][1];

        $result = $html . "\n" . implode("\n", $this->hiddens);
        if( $return )
            return $result;

        echo $result;
    }

    public static function render_input( &$field, $active, $for_table = false )
    {
        $defaults = array(
            'type'              => 'text',
            'label'             => '',
            'description'       => isset($field['desc']) ? $field['desc'] : '',
            'placeholder'       => '',
            'maxlength'         => false,
            'required'          => false,
            'autocomplete'      => false,
            'id'                => '',
            'name'              => $field['id'],
            // 'class'             => array(),
            'label_class'       => array('label'),
            'input_class'       => array(),
            'options'           => array(),
            'custom_attributes' => array(),
            // 'validate'          => array(),
            'default'           => '',
            'before'            => '',
            'after'             => '',
            'check_active'      => false,
            'value'             => '',
            );

        $field = wp_parse_args( $field, $defaults );

        if( ! in_array($field['type'], array('checkbox', 'select', 'radio')) )
            $field['placeholder'] = $field['default'];

        $field['id'] = str_replace('][', '_', $field['id']);
        $entry = self::parse_entry($field, $active, $field['value']);

        return self::_input_template( $field, $entry, $for_table );
    }

    /******************************** Templates *******************************/
    private function _field_template( $field, $input, $for_table )
    {
        // if ( $field['required'] ) {
        //     $field['class'][] = 'required';
        //     $required = ' <abbr class="required" title="' . esc_attr__( 'required' ) . '">*</abbr>';
        // } else {
        //     $required = '';
        // }

        $html = array();

        $desc = '';
        if( $field['description'] ){
            if( isset($this->args['hide_desc']) && $this->args['hide_desc'] === true )
                $desc = "<div class='description' style='display: none;'>{$field['description']}</div>";
            else
                $desc = "<span class='description'>{$field['description']}</span>";
        }

        $template = $field['before'] . $this->args['item_wrap'][0];
        $template.= $input;
        $template.= $this->args['item_wrap'][1] . $field['after'];
        $template.= $desc;

        if( ! $this->is_table ){
            $html[] = $template;
        }
        elseif( $field['type'] == 'hidden' ){
            $this->hiddens[] = $input;
        }
        elseif( $field['type'] == 'html' ){
            $html[] = $this->args['form_wrap'][1];
            $html[] = $input;
            $html[] = $this->args['form_wrap'][0];
        }
        else {
            $lc = implode( ' ', $field['label_class'] );
            $html[] = "<tr id='{$field['id']}'>";
            // @todo : add required symbol
            $html[] = "  <{$this->args['label_tag']} class='label'>";
            $html[] = "    {$field['label']}";
            $html[] = "  </{$this->args['label_tag']}>";

            $html[] = "  <td>";
            $html[] = "    " . $template;
            $html[] = "  </td>";
            $html[] = "</tr>";
        }

        return implode("\n", $html);
    }

    private static function _input_template( $field, $entry, $for_table = false )
    {
        $name         = 'name="' . esc_attr( $field['name'] ) . '"';
        $id           = 'id="' . esc_attr( $field['id'] ) . '"';
        $class        = sizeof($field['input_class']) ?
            ' ' . esc_attr( implode( ' ', $field['input_class'] ) ) : '';
        $ph           = esc_attr( $field['placeholder'] );

        $custom_attributes = array();
        if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
            foreach ( $field['custom_attributes'] as $attribute => $attribute_value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
            }
        }
        $attrs = implode( ' ', $custom_attributes );

        $maxlength = ( $field['maxlength'] ) ?
            'maxlength="' . absint( $field['maxlength'] ) . '"' : '';
        $autocomplete = ( $field['autocomplete'] ) ?
            'autocomplete="' . esc_attr( $field['autocomplete'] ) . '"' : '';

        $label = ( ! $for_table && $field['label'] ) ?
            "<label for='".esc_attr($field['id'])."'> {$field['label']} </label>" : '';

        $input = '';
        switch ($field['type']) {
            // @todo : add fieldset
            case 'html' :
                $input .= $field['value'];
                break;
            case 'textarea' :
                $rows = empty( $args['custom_attributes']['rows'] ) ? ' rows="5"' : '';
                $cols = empty( $args['custom_attributes']['cols'] ) ? ' cols="40"' : '';

                $input .= $label;
                $input .= "<textarea ";
                $input .= "{$name} {$id}{$cols}{$rows} {$ph} {$attrs} {$autocomplete} {$maxlength}";
                $input .= "class='input-text{$class}'>";
                $input .= esc_textarea( $entry );
                $input .= '</textarea>';
                break;
            case 'checkbox' :
                $val = $field['value'] ? $field['value'] : 1;
                $checked = checked( $entry, $val, false );

                // if $clear_value === false dont use defaults (couse default + empty value = true)
                if( false !== self::$clear_value )
                    $input .= "<input type='hidden' {$name} value='{self::$clear_value}'>\n";

                $input .= "<input type='checkbox' {$name} {$id} value='{$val}'";
                $input .= " class='input-checkbox{$class}' {$checked} />";
                $input .= $label;
                break;
            case 'password' :
            case 'text' :
            case 'email' :
            case 'tel' :
            case 'number' :
                $type = "type='" . esc_attr( $field['type'] ) . "'";
                $val = esc_attr( $entry );

                $input .= $label;
                $input .= "<input {$type} {$name} {$id} {$ph} {$maxlength} {$autocomplete}";
                $input .= " class='input-text{$class}' value='{$val}' {$attrs} />";
                break;
            case 'select' :
                $options = '';

                if ( ! empty( $field['options'] ) ) {
                    $input .= $label;

                    if( isset($field['value']) && $field['value']!==false && $field['value']!==NULL )
                        $entry = $field['value'];

                    foreach ( $field['options'] as $option_key => $option_text ) {
                        if ( '' === $option_key ) {
                            if ( empty( $field['placeholder'] ) )
                                $field['placeholder'] = $option_text ?
                                    $option_text : __( 'Choose an option' );

                            // $custom_attributes[] = 'data-allow_clear="true"';
                        }

                        if( ! is_array( $option_text ) ){
                            $options .= '<option value="' . esc_attr( $option_key ) . '" ' .
                                selected( $entry, $option_key, false ) . '>' .
                                esc_attr( $option_text ) . '</option>';
                        }
                        else {
                            $options .= "<optgroup label='{$option_key}'>";
                            foreach ($option_key as $sub_option_key => $sub_option_text) {
                                $options .= '<option value="' . esc_attr( $sub_option_key ) . '" ' .
                                    selected( $entry, $sub_option_key, false ) . '>' .
                                    esc_attr( $sub_option_text ) . '</option>';
                            }
                            $options .= "</optgroup>";
                        }
                    }
                    $input .= "<select {$name} {$id} class='select{$class}' {$attrs}";
                    $input .= " {$autocomplete}>{$options}</select>";
                }
                break;
            // case 'radio' :

            //     $label_id = current( array_keys( $args['options'] ) );

            //     if ( ! empty( $args['options'] ) ) {
            //         foreach ( $args['options'] as $option_key => $option_text ) {
            //             $field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) .'" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
            //             $field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) .'">' . $option_text . '</label>';
            //         }
            //     }

            //     break;
            }
        return $input;
    }

    /********************************** Utils *********************************/
    private static function parse_defaults($args, $is_table)
    {
        $defaults = array(
            'admin_page'  => false, // set true for auto detect
            'item_wrap'   => array('<p>', '</p>'),
            'form_wrap'   => array('', ''),
            'label_tag'   => 'th',
            'hide_desc'   => false,
        );

        if( $is_table )
            $defaults['form_wrap'] = array('<table class="table form-table"><tbody>', '</tbody></table>');

        if( ( isset($args['admin_page']) && $args['admin_page'] !== false ) ||
            !isset($args['admin_page']) && is_admin() && !empty($_GET['page']) )
            $defaults['admin_page'] = $_GET['page'];

        $args = wp_parse_args( $args, $defaults );

        if( ! is_array($args['item_wrap']) )
            $args['item_wrap'] = array('', '');

        if( ! is_array($args['form_wrap']) )
            $args['form_wrap'] = array('', '');

        if( false === $is_table )
            $args['label_tag'] = 'label';

        return $args;
    }

    private static function parse_entry($field, $active)
    {
        if( ! is_array($active) || sizeof($active) < 1 )
            return false;

        $active_key = $field['check_active'] ? $field[$field['check_active']] : str_replace('[]', '', $field['name']);
        $active_value = isset($active[$active_key]) ? $active[$active_key] : false;

        if($field['type'] == 'checkbox' || $field['type'] == 'radio'){
            $entry = self::is_checked( $field, $active_value );
        }
        elseif($field['type'] == 'select'){
            $entry = ($active_value) ? $active_value : $field['default'];
        }
        else {
            // if text, textarea, number, email..
            $entry = $active_value;
        }
        return $entry;
    }

    private static function is_checked( $field, $active )
    {
        // if( $active === false && $value )
          // return true;

        $checked = ( $active === false ) ? false : true;
        if( $active === 'false' || $active === 'off' || $active === '0' )
            $active = false;

        if( $active === 'true'  || $active === 'on'  || $active === '1' )
            $active = true;

        if( $active || $field['default'] ){
            if( $field['value'] ){
                if( is_array($active) ){
                    if( in_array($field['value'], $active) )
                        return true;
                }
                else {
                    if( $field['value'] == $active || $field['value'] === true )
                        return true;
                }
            }
            else {
                if( $active || (!$checked && $field['default']) )
                    return true;
            }

            return false;
        }
    }

    private static function admin_page_options( $fields, $option_name )
    {
        foreach ($fields as &$field) {
          if ( ! isset($field['id']) && ! isset($field['name']) )
            continue;

        if( isset($field['name']) )
            $field['name'] = "{$option_name}[{$field['name']}]";
        else
            $field['name'] = "{$option_name}[{$field['id']}]";

        if( !isset($field['check_active']) )
            $field['check_active'] = 'id';
        }

        return $fields;
    }
}
// public static function render_fieldset( $input, $entry, $is_table, $label = '' ){
//     $result = '';

//     // <legend>Работа со временем</legend>

//     foreach ($input['fields'] as $field) {
//       if( !isset($field['name']) )
//         $field['name'] = _isset_empty($field['id']);

//       $field['id'] = str_replace('][', '_', $field['id']);

//       $f_name = self::get_function_name($field['type']);
//       $result .= self::$f_name( $field, $entry, $is_table, $label );
//     }
//     return $result;
//   }

  // /**
  //  * EXPEREMENTAL!
  //  * Get ID => Default values from $render_data
  //  * @param  array() $render_data
  //  * @return array(array(ID=>default),ar..)
  //  */
  // public static function defaults( $render_data ){
  //   $defaults = array();
  //   if(empty($render_data))
  //     return $defaults;

  //   if( isset($render_data['id']) )
  //       $render_data = array($render_data);

  //   foreach ($render_data as $input) {
  //     if(isset($input['default']) && $input['default']){
  //       $input['id'] = str_replace('][', '_', $input['id']);
  //       $defaults[$input['id']] = $input['default'];
  //     }
  //   }

  //   return $defaults;
  // }

  // /**
  //  * @todo: add recursive handle
  //  *
  //  * @param  string   $option_name
  //  * @param  string   $sub_name         $option_name[$sub_name]
  //  * @param  boolean  $is_admin_options recursive split value array key with main array
  //  * @param  int|bool $postmeta         int = post_id for post meta, true = get post_id from global post
  //  * @return array                      installed options
  //  */
  // public static function active($option, $sub_name = false, $is_admin_options = false, $postmeta = false){

  //   global $post;

  //   /** get active values */
  //   if( is_string($option) ){
  //     if( $postmeta ){
  //       if( !is_int($postmeta) && !isset($post->ID) )
  //         return false;

  //       $post_id = ($postmeta === true) ? $post->ID : $postmeta;

  //       $active = get_post_meta( $post_id, $option, true );
  //     }
  //     else {
  //       $active = get_option( $option, array() );
  //     }
  //   }
  //   else {
  //     $active = $option;
  //   }

  //   /** get subvalue */
  //   if( $sub_name && isset($active[$sub_name]) && is_array($active[$sub_name]) )
  //     $active = $active[$sub_name];
  //   elseif( $sub_name && !isset($active[$sub_name]) )
  //     return false;

  //   /** if active not found */
  //   if( !isset($active) || !is_array($active) )
  //       return false;

  //   /** sanitize admin values */
  //   if( $is_admin_options === true ){
  //     $result = array();
  //     foreach ($active as $key => $value) {
  //       if( is_array($value) ){
  //         foreach ($value as $key2 => $value2) {
  //           $result[$key . '_' . $key2] = $value2;
  //         }
  //       }
  //       else {
  //         $result[$key] = $value;
  //       }
  //     }

  //     return $result;
  //   }

  //   return $active;
  // }