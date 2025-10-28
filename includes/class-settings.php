<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class IRANK_CC_Settings {
    public static function init() {
        register_setting( 'irank_cc_group', 'irank_cc_options', array(
            'type' => 'array',
            'sanitize_callback' => array(__CLASS__, 'sanitize'),
            'default' => irank_cc_default_options(),
        ) );

        add_settings_section( 'irank_cc_main', __( 'Defaults', 'irank-calc-cards' ), '__return_false', 'irank-cc-settings' );

        self::add_field( 'min_weight', __( 'Minimum Weight', 'irank-calc-cards' ), 'number' );
        self::add_field( 'max_weight', __( 'Maximum Weight', 'irank-calc-cards' ), 'number' );
        self::add_field( 'step', __( 'Slider Step', 'irank-calc-cards' ), 'number' );
        self::add_field( 'loss_factor', __( 'Loss Factor (e.g., 0.15)', 'irank-calc-cards' ), 'text' );
        self::add_field( 'unit', __( 'Unit (lbs or kg)', 'irank-calc-cards' ), 'text' );
        self::add_field( 'gradient_start', __( 'Gradient Start Color', 'irank-calc-cards' ), 'text' );
        self::add_field( 'gradient_end', __( 'Gradient End Color', 'irank-calc-cards' ), 'text' );
        self::add_field( 'nohemi_css_url', __( 'Nohemi CSS URL (optional)', 'irank-calc-cards' ), 'text' );
        self::add_field( 'tracking_enabled', __( 'Enable Tracking', 'irank-calc-cards' ), 'checkbox' );
    }

    private static function add_field( $key, $label, $type ) {
        add_settings_field( $key, $label, array(__CLASS__, 'render_field'), 'irank-cc-settings', 'irank_cc_main', array(
            'key' => $key,
            'type' => $type,
        ) );
    }

    public static function sanitize( $input ) {
        $out = irank_cc_default_options();
        $out['min_weight'] = isset($input['min_weight']) ? (int)$input['min_weight'] : $out['min_weight'];
        $out['max_weight'] = isset($input['max_weight']) ? (int)$input['max_weight'] : $out['max_weight'];
        $out['step'] = isset($input['step']) ? (int)$input['step'] : $out['step'];
        $out['loss_factor'] = isset($input['loss_factor']) ? (float)$input['loss_factor'] : $out['loss_factor'];
        $out['unit'] = isset($input['unit']) ? sanitize_text_field($input['unit']) : $out['unit'];
        $out['gradient_start'] = isset($input['gradient_start']) ? sanitize_text_field($input['gradient_start']) : $out['gradient_start'];
        $out['gradient_end'] = isset($input['gradient_end']) ? sanitize_text_field($input['gradient_end']) : $out['gradient_end'];
        $out['nohemi_css_url'] = isset($input['nohemi_css_url']) ? esc_url_raw($input['nohemi_css_url']) : $out['nohemi_css_url'];
        $out['tracking_enabled'] = !empty($input['tracking_enabled']) ? 1 : 0;
        return $out;
    }

    public static function render_field( $args ) {
        $opts = get_option( 'irank_cc_options', irank_cc_default_options() );
        $key = $args['key'];
        $type = $args['type'];
        $val = isset($opts[$key]) ? $opts[$key] : '';
        $name = 'irank_cc_options['.$key.']';
        if ( $type === 'checkbox' ) {
            echo '<label><input type="checkbox" name="'.esc_attr($name).'" value="1" '.checked( $val, 1, false ).'> ' . esc_html__( 'Enabled', 'irank-calc-cards' ) . '</label>';
        } else {
            $extra = '';
            if ( $key === 'nohemi_css_url' ) {
                $extra = ' placeholder="https://use.typekit.net/xxxxxxx.css" title="Paste your licensed Nohemi cloud CSS URL (Adobe Fonts/Typekit or your CDN), then Save settings and hard-refresh the editor and frontend."';
            }
            printf('<input type="%s" class="regular-text" name="%s" value="%s"%s/>', esc_attr($type), esc_attr($name), esc_attr($val), $extra);
            if ( $key === 'nohemi_css_url' ) {
                echo '<p class="description">'.
                    esc_html__('How to use:', 'irank-calc-cards').
                    ' '.
                    esc_html__('Get the cloud CSS link for your licensed Nohemi kit (e.g., Adobe Fonts/Typekit or your CDN). Paste the URL here. Click Save Changes, then hard-refresh the editor and frontend.', 'irank-calc-cards') .
                '</p>';
            }
        }
    }

    public static function render_page() {
        echo '<div class="wrap"><h1>'.esc_html__('IRANK Calc & Cards Settings','irank-calc-cards').'</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'irank_cc_group' );
        do_settings_sections( 'irank-cc-settings' );
        submit_button();
        echo '</form></div>';
    }
}
