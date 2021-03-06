<?php
/**
 * @package Pods\Fields
 */
class PodsField_Number extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Number';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'number';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Plain Number';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%d';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @return array
     *
     * @since 2.0
     */
    public function options () {
        $options = array(
            'number_repeatable' => array(
                'label' => __( 'Repeatable Field', 'pods' ),
                'default' => 0,
                'type' => 'boolean',
                'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
                'boolean_yes_label' => '',
                'dependency' => true,
                'developer_mode' => true
            ),
            'number_format_type' => array(
                'label' => __( 'Input Type', 'pods' ),
                'default' => 'number',
                'type' => 'pick',
                'data' => array(
                    'number' => __( 'Freeform Number', 'pods' ),
                    'slider' => __( 'Slider', 'pods' )
                ),
                'dependency' => true
            ),
            'number_format' => array(
                'label' => __( 'Format', 'pods' ),
                'default' => 'i18n',
                'type' => 'pick',
                'data' => array(
                    'i18n' => __( 'Localized Default', 'pods' ),
                    '9,999.99' => '1,234.00',
                    '9999.99' => '1234.00',
                    '9.999,99' => '1.234,00',
                    '9999,99' => '1234,00'
                )
            ),
            'number_decimals' => array(
                'label' => __( 'Decimals', 'pods' ),
                'default' => 0,
                'type' => 'number'
            ),
            'number_step' => array(
                'label' => __( 'Slider Increment (Step)', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'slider' ),
                'default' => 1,
                'type' => 'text'
            ),
            'number_min' => array(
                'label' => __( 'Minimum Number', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'slider' ),
                'default' => 0,
                'type' => 'text'
            ),
            'number_max' => array(
                'label' => __( 'Maximum Number', 'pods' ),
                'depends-on' => array( 'number_format_type' => 'slider' ),
                'default' => 100,
                'type' => 'text'
            ),
            'number_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 12,
                'type' => 'number'
            )/*,
            'number_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
                )
            )*/
        );
        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $length = (int) pods_var( 'number_max_length', $options, 12, null, true );

        if ( $length < 1 || 64 < $length )
            $length = 64;

        $decimals = (int) pods_var( 'number_decimals', $options, 2, null, true );

        if ( $decimals < 1 )
            $decimals = 0;
        elseif ( 30 < $decimals )
            $decimals = 30;

        if ( $length < $decimals )
            $decimals = $length;

        $schema = 'DECIMAL(' . $length . ',' . $decimals . ')';

        return $schema;
    }

    /**
     * Define the current field's preparation for sprintf
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function prepare ( $options = null ) {
        $format = self::$prepare;

        if ( 0 < (int) pods_var( 'number_decimals', $options, 0 ) )
            $format = '%01.' . (int) pods_var( 'number_decimals', $options ) . 'f';

        return $format;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null|string
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $value = $this->format( $value, $name, $options, $pod, $id );

        return $value;
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        if ( is_array( $value ) )
            $value = implode( '', $value );

        if ( 'slider' == pods_var( 'number_format_type', $options, 'number' ) )
            $field_type = 'slider';
        else
            $field_type = 'number';

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Build regex necessary for JS validation
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @return bool|string
     * @since 2.0
     */
    public function regex ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        global $wp_locale;

        if ( '9999.99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = '.';
        }
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        else {
            $thousands = $wp_locale->number_format[ 'thousands_sep' ];
            $dot = $wp_locale->number_format[ 'decimal_point' ];
        }

        return '\-*[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';
    }

    /**
     * Validate a value before it's saved
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     * @param null $params
     *
     * @return bool|mixed|void
     * @since 2.0
     */
    public function validate ( &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        global $wp_locale;

        if ( '9999.99' == pods_var( 'number_format', $options ) ) {
            $thousands = ',';
            $dot = '.';
        }
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        else {
            $thousands = $wp_locale->number_format[ 'thousands_sep' ];
            $dot = $wp_locale->number_format[ 'decimal_point' ];
        }

        $check = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

        $check = preg_replace( '/[0-9\.\-]/', '', $check );

        $label = pods_var( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

        if ( 0 < strlen( $check ) )
            return sprintf( __( '%s is not numeric', 'pods' ), $label );

        return true;
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @return mixed|string
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        global $wp_locale;

        if ( '9999.99' == pods_var( 'number_format', $options ) ) {
            $thousands = ',';
            $dot = '.';
        }
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        else {
            $thousands = $wp_locale->number_format[ 'thousands_sep' ];
            $dot = $wp_locale->number_format[ 'decimal_point' ];
        }

        $value = str_replace( array( $thousands, $dot ), array( '', '.' ), $value );

        $value = preg_replace( '/[^0-9\.]/', '', $value );

        $decimals = pods_absint( (int) pods_var( 'number_decimals', $options, 0, null, true ) );

        $value = number_format( (float) $value, $decimals, '.', '' );

        return $value;
    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @return mixed|null|string
     * @since 2.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        return $this->display( $value, $name, $options, $pod, $id );
    }

    /**
     * Reformat a number to the way the value of the field is displayed
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return string
     * @since 2.0
     */
    public function format ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        global $wp_locale;

        if ( '9999.99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = '.';
        }
        elseif ( '9999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '';
            $dot = ',';
        }
        elseif ( '9.999,99' == pods_var( 'number_format', $options ) ) {
            $thousands = '.';
            $dot = ',';
        }
        else {
            $thousands = $wp_locale->number_format[ 'thousands_sep' ];
            $dot = $wp_locale->number_format[ 'decimal_point' ];
        }

        if ( 'i18n' == pods_var( 'number_format', $options ) )
            $value = number_format_i18n( (float) $value, (int) pods_var( 'number_decimals', $options ) );
        else
            $value = number_format( (float) $value, (int) pods_var( 'number_decimals', $options ), $dot, $thousands );

        return $value;
    }
}
