<?php

namespace CDevelopers\media;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

/**
 * @todo: add defaults
 * @todo : add checkbox reverse defaults
 */
class WP_Admin_Forms {
    static $clear_value = false;
    protected $inputs, $args, $is_table, $active;
    protected $hiddens = array();

    public function __construct($data = null, $is_table = true, $args = null)
    {
        if( ! is_array($data) )
            $data = array();

        if( ! is_array($args) )
            $args = array();

        if( isset($data['id']) || isset($data['name']) )
            $data = array($data);

        $args = self::parse_defaults($args, $is_table);

        $this->fields = self::admin_page_options( $data, $args['form_name'], $args['sub_name'] );
        $this->args = $args;
        $this->is_table = $is_table;
    }

    public function render( $return=false )
    {
        $this->get_active();

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

    public function set_active( $active )
    {
        $this->active = $active;
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
            'check_active'      => 'id',
            'value'             => '',
            );

        $field = wp_parse_args( $field, $defaults );
        $field['id'] = str_replace('][', '_', $field['id']);

        if( "" !== $field['default'] && ! in_array($field['type'], array('checkbox', 'radio')) ) {
            if ($field['type'] != 'select')
                $field['placeholder'] = $field['default'];
        }

        $key = $field[ $field['check_active'] ];
        if( "" !== $field['default'] && ! isset($active[ $key ]) ) {
            $active[ $key ] = $field['default'];
        }

        $entry = self::parse_entry($field, $active, $field['value']);

        return self::_input_template( $field, $entry, $for_table );
    }

    public function get_active()
    {
        if( ! $this->active ) {
            $this->active = $this->_active();
        }

        return $this->active;
    }

    public static function defaults( $render_data = null, $args = array() ) {
        $render_data = (array) $render_data;
        $defaults = array();

        if( empty($render_data) ) return $defaults;

        if( isset($render_data['id']) ) {
            $render_data = array($render_data);
        }

        $args = wp_parse_args( $args, array(
            'form_name' => '',
            'sub_name'  => false,
            ) );

        $fields = self::admin_page_options( $render_data, $args['form_name'], $args['sub_name'] );

        foreach ($fields as $field) {
            $defaults[ $field['id'] ] = isset( $field['default'] ) ? $field['default'] : '';
        }

        return $defaults;
    }

    /**
     * @return array installed options
     */
    private function _active()
    {
        $active = array();
        switch ( $this->args['mode'] ) {
            case 'post':
                if( ! $post_id = (int) $this->args['post_id'] ) {
                    $post_id = get_the_ID();
                }

                if( $post_id <= 0 ) return $active;

                $metaname = $this->args['sub_name'] ? $this->args['sub_name'] : $this->args['form_name'];
                $active = get_post_meta( $post_id, $metaname, true );
                break;

            case 'page':
            default:
                $active = get_option( $this->args['form_name'], array() );

                if( $sub_name = $this->args['sub_name'] ) {
                    $active = isset($active[ $sub_name ]) ? $active[ $sub_name ] : false;
                }
                break;
        }

        /** if active not found */
        if( ! is_array($active) || ! count($active) ) {
            return array();
        }

        $result = array();
        foreach ($active as $key => $value) {
            if( is_array($value) ) {
                foreach ($value as $key2 => $value2) {
                    $result[ $key . '_' . $key2 ] = $value2;
                }
            }
            else {
                $result[ $key ] = $value;
            }
        }

        return $result;
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
        $name = 'name="' . esc_attr( $field['name'] ) . '"';
        $id   = 'id="' . esc_attr( $field['id'] ) . '"';

        $class = '';
        if( is_array($field['input_class']) ) {
            $class = esc_attr( implode( ' ', $field['input_class'] ) );
        }
        elseif( is_string($field['input_class']) ) {
            $class = ' ' . esc_attr( $field['input_class'] );
        }

        $ph           = 'placeholder="' . esc_attr( $field['placeholder'] ) . '"';

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
                $rows = empty( $field['custom_attributes']['rows'] ) ? ' rows="5"' : '';
                $cols = empty( $field['custom_attributes']['cols'] ) ? ' cols="40"' : '';

                $input .= $label;
                $input .= "<textarea ";
                $input .= "{$name} {$id}{$cols}{$rows} {$ph} {$attrs} {$autocomplete} {$maxlength}";
                $input .= "class='input-text{$class}'>";
                $input .= esc_textarea( $entry );
                $input .= '</textarea>';
                break;
            case 'checkbox' :
                $val = $field['value'] ? $field['value'] : 'on';
                $checked = checked( $entry, true, false );
                // if( $field['default'] ) {
                //     if( ! $entry ) {
                //         $checked = checked( in_array($entry, array('true', '1', 'on')), true, false );
                //     }
                //     $clear_value = 'false';
                // }

                if( $field['default'] ) {
                    $clear_value = str_replace(
                        array('true', '1', 'on', 'Y'),
                        array('false', '0', 'off', 'N'),
                        $field['default'] );
                }

                if( isset($clear_value) || false !== ($clear_value = self::$clear_value) ) {
                    $input .= "<input type='hidden' {$name} value='{$clear_value}'>\n";
                }

                $input .= "<input type='checkbox' {$name} {$id} {$attrs} value='{$val}'";
                $input .= " class='input-checkbox{$class}' {$checked} />";
                $input .= $label;
                break;
            case 'hidden' :
            case 'password' :
            case 'text' :
            case 'email' :
            case 'tel' :
            case 'number' :
                $type = sprintf('type="%s"', esc_attr( $field['type'] ));
                $val = $field['value'] ? esc_attr( $field['value'] ) : esc_attr( $entry );

                $input .= $label;
                $input .= "<input {$type} {$name} {$id} {$ph} {$maxlength} {$autocomplete}";
                $input .= " class='input-text{$class}' value='{$val}' {$attrs} />";
                break;
            case 'select' :
                $options = '';

                if ( ! empty( $field['options'] ) ) {
                    $input .= $label;

                    // if( $field['value'] || $field['value'] === '' ) {
                    //     $entry = $field['value'];
                    // }

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
                            foreach ($option_text as $sub_option_key => $sub_option_text) {
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

            //     $label_id = current( array_keys( $field['options'] ) );

            //     if ( ! empty( $field['options'] ) ) {
            //         foreach ( $field['options'] as $option_key => $option_text ) {
            //             $field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $field['input_class'] ) ) .'" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $field['id'] ) . '_' . esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) . ' />';
            //             $field .= '<label for="' . esc_attr( $field['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $field['label_class'] ) .'">' . $option_text . '</label>';
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
            'form_name'   => '',
            'mode'        => 'page', // post
            'post_id'     => '', // force post id for post meta mode
            'sub_name'    => '',
            // template:
            'item_wrap'   => array('<p>', '</p>'),
            'form_wrap'   => array('', ''),
            'label_tag'   => 'th',
            'hide_desc'   => false,
        );

        if( $is_table )
            $defaults['form_wrap'] = array('<table class="table form-table"><tbody>', '</tbody></table>');

        $args = wp_parse_args( $args, $defaults );

        if( ! $args['form_name'] ) {
            $args['form_name'] = ( ! empty($_GET['page']) ) ? $_GET['page'] : $args['mode'];
        }

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

        $active_key = $field['check_active'] ? $field[ $field['check_active'] ] : str_replace('[]', '', $field['name']);
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
            return false;

        if( $active === 'true'  || $active === 'on'  || $active === '1' )
            return true;

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
        }

        return false;
    }

    private static function admin_page_options( $fields, $option_name, $sub_name = false )
    {
        foreach ($fields as &$field) {
            if ( ! isset($field['id']) && ! isset($field['name']) )
                continue;

            if( $option_name ) {
                if( ! isset($field['name']) ) {
                    $field['name'] = $field['id'];
                }

                if( $sub_name )
                    $field['name'] = sprintf('%s[%s][%s]', $option_name, $sub_name, $field['id']);
                else
                    $field['name'] = sprintf('%s[%s]', $option_name, $field['id']);
            }
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
