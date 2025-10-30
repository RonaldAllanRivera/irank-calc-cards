<?php
/**
 * Plugin Name: IRANK Calc & Cards
 * Description: Weight loss calculator and product cards as no-build Gutenberg blocks with same-page results and first-party tracking.
 * Version: 0.1.4
 * Author: Ronald Allan Rivera
 * Author URI: https://github.com/RonaldAllanRivera/irank-calc-cards
 * Requires at least: 6.8
 * Requires PHP: 8.1
 * Text Domain: irank-calc-cards
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'IRANK_CC_VER', '0.1.4' );
define( 'IRANK_CC_DIR', plugin_dir_path( __FILE__ ) );
define( 'IRANK_CC_URL', plugin_dir_url( __FILE__ ) );

function irank_cc_default_options() {
    return array(
        'min_weight' => 100,
        'max_weight' => 400,
        'step' => 1,
        'loss_factor' => 0.15,
        'unit' => 'lbs',
        'gradient_start' => '#FFBB8E',
        'gradient_end' => '#f67a51',
        'nohemi_css_url' => '',
    );
}

function irank_cc_activate() {
    $opt = get_option( 'irank_cc_options' );
    if ( ! is_array( $opt ) ) update_option( 'irank_cc_options', irank_cc_default_options() );

    global $wpdb;
    $table = $wpdb->prefix . 'irank_calc_events';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        page_id BIGINT UNSIGNED NULL,
        weight FLOAT NULL,
        loss FLOAT NULL,
        session_id VARCHAR(64) NULL,
        referrer TEXT NULL,
        user_agent TEXT NULL,
        ip_hash CHAR(64) NULL,
        variant VARCHAR(64) NULL,
        PRIMARY KEY  (id),
        KEY created_at (created_at)
    ) $charset_collate;";
    dbDelta( $sql );

    // Leads table
    $table2 = $wpdb->prefix . 'irank_calc_leads';
    $sql2 = "CREATE TABLE $table2 (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at DATETIME NOT NULL,
        page_id BIGINT UNSIGNED NULL,
        full_name VARCHAR(190) NULL,
        email VARCHAR(190) NULL,
        phone VARCHAR(50) NULL,
        weight FLOAT NULL,
        loss FLOAT NULL,
        session_id VARCHAR(64) NULL,
        referrer TEXT NULL,
        user_agent TEXT NULL,
        ip_hash CHAR(64) NULL,
        PRIMARY KEY (id),
        KEY created_at (created_at)
    ) $charset_collate;";
    dbDelta( $sql2 );
}
register_activation_hook( __FILE__, 'irank_cc_activate' );

function irank_cc_register_settings_page() {
    require_once IRANK_CC_DIR . 'includes/class-settings.php';
    IRANK_CC_Settings::init();
}
add_action( 'admin_init', 'irank_cc_register_settings_page' );

function irank_cc_admin_menu() {
    add_options_page( 'IRANK Calc & Cards', 'IRANK Calc & Cards', 'manage_options', 'irank-cc-settings', 'irank_cc_render_settings_page' );
}
add_action( 'admin_menu', 'irank_cc_admin_menu' );

function irank_cc_render_settings_page() {
    IRANK_CC_Settings::render_page();
}

function irank_cc_admin_reports_menu() {
    add_submenu_page(
        'tools.php',
        'IRANK Reports',
        'IRANK Reports',
        'manage_options',
        'irank-cc-reports',
        'irank_cc_render_reports_page'
    );
}
add_action( 'admin_menu', 'irank_cc_admin_reports_menu' );

function irank_cc_admin_leads_menu() {
    add_submenu_page(
        'tools.php',
        'IRANK Leads',
        'IRANK Leads',
        'manage_options',
        'irank-cc-leads',
        'irank_cc_render_leads_page'
    );
}
add_action( 'admin_menu', 'irank_cc_admin_leads_menu' );

function irank_cc_render_reports_page() {
    if ( ! current_user_can('manage_options') ) return;
    global $wpdb; $table = $wpdb->prefix.'irank_calc_events';
    $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
    $recent = $wpdb->get_results("SELECT DATE(created_at) d, COUNT(*) c FROM $table GROUP BY DATE(created_at) ORDER BY d DESC LIMIT 30", ARRAY_A);
    $export_url = wp_nonce_url( admin_url('admin-post.php?action=irank_cc_export_csv'), 'irank_cc_export' );
    echo '<div class="wrap"><h1>IRANK Reports</h1>';
    echo '<p>Total events: <strong>'.esc_html($total).'</strong></p>';
    echo '<p><a class="button" href="'.esc_url($export_url).'">Export CSV</a></p>';
    echo '<table class="widefat"><thead><tr><th>Date</th><th>Count</th></tr></thead><tbody>';
    if ($recent){ foreach($recent as $r){ echo '<tr><td>'.esc_html($r['d']).'</td><td>'.esc_html($r['c']).'</td></tr>'; } }
    else { echo '<tr><td colspan="2">No data yet.</td></tr>'; }
    echo '</tbody></table></div>';
}

function irank_cc_export_csv() {
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('irank_cc_export');
    global $wpdb; $table = $wpdb->prefix.'irank_calc_events';
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);
    nocache_headers();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="irank-events.csv"');
    $out = fopen('php://output','w');
    if ($rows){ fputcsv($out, array_keys($rows[0])); foreach($rows as $row){ fputcsv($out,$row); } }
    fclose($out); exit;
}
add_action('admin_post_irank_cc_export_csv','irank_cc_export_csv');

function irank_cc_render_leads_page(){
    if ( ! current_user_can('manage_options') ) return;
    global $wpdb; $table = $wpdb->prefix.'irank_calc_leads';
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC LIMIT 200", ARRAY_A);
    $export_url = wp_nonce_url( admin_url('admin-post.php?action=irank_cc_export_leads'), 'irank_cc_export_leads' );
    echo '<div class="wrap"><h1>IRANK Leads</h1>';
    echo '<p><a class="button" href="'.esc_url($export_url).'">Export CSV</a></p>';
    echo '<table class="widefat"><thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Phone</th><th>Page</th><th>Weight</th><th>Loss</th></tr></thead><tbody>';
    if($rows){ foreach($rows as $r){
        echo '<tr>'
            .'<td>'.esc_html($r['created_at']).'</td>'
            .'<td>'.esc_html($r['full_name']).'</td>'
            .'<td>'.esc_html($r['email']).'</td>'
            .'<td>'.esc_html($r['phone']).'</td>'
            .'<td>'.esc_html($r['page_id']).'</td>'
            .'<td>'.esc_html($r['weight']).'</td>'
            .'<td>'.esc_html($r['loss']).'</td>'
            .'</tr>';
    } } else { echo '<tr><td colspan="7">No leads yet.</td></tr>'; }
    echo '</tbody></table></div>';
}

function irank_cc_export_leads(){
    if ( ! current_user_can('manage_options') ) wp_die('Forbidden');
    check_admin_referer('irank_cc_export_leads');
    global $wpdb; $table = $wpdb->prefix.'irank_calc_leads';
    $rows = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);
    nocache_headers();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="irank-leads.csv"');
    $out = fopen('php://output','w');
    if($rows){ fputcsv($out, array_keys($rows[0])); foreach($rows as $row){ fputcsv($out,$row); } }
    fclose($out); exit;
}
add_action('admin_post_irank_cc_export_leads','irank_cc_export_leads');

function irank_cc_register_assets() {
    $v = function($rel){ $p = IRANK_CC_DIR . $rel; return file_exists($p) ? filemtime($p) : IRANK_CC_VER; };
    // Fonts: Poppins (Google Fonts). Nohemi is not bundled; will fall back.
    wp_register_style( 'irank-cc-fonts', 'https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap', array(), null );
    $opts = get_option( 'irank_cc_options', irank_cc_default_options() );
    $deps = array('irank-cc-fonts');
    if ( ! empty( $opts['nohemi_css_url'] ) ) {
        wp_register_style( 'irank-cc-nohemi', esc_url_raw($opts['nohemi_css_url']), array(), null );
        $deps[] = 'irank-cc-nohemi';
    }
    wp_register_style( 'irank-cc-editor', IRANK_CC_URL . 'assets/css/editor.css', $deps, $v('assets/css/editor.css') );
    wp_register_style( 'irank-cc-frontend', IRANK_CC_URL . 'assets/css/frontend.css', $deps, $v('assets/css/frontend.css') );
    wp_register_style( 'irank-cc-cards', IRANK_CC_URL . 'assets/css/cards.css', array('irank-cc-frontend'), $v('assets/css/cards.css') );

    wp_register_script( 'irank-cc-editor-calculator', IRANK_CC_URL . 'assets/js/editor.calculator.js', array( 'wp-blocks','wp-element','wp-components','wp-i18n','wp-block-editor','wp-editor' ), $v('assets/js/editor.calculator.js'), true );
    wp_register_script( 'irank-cc-editor-cards', IRANK_CC_URL . 'assets/js/editor.cards.js', array( 'wp-blocks','wp-element','wp-components','wp-i18n','wp-block-editor','wp-editor' ), $v('assets/js/editor.cards.js'), true );

    wp_register_script( 'irank-cc-frontend-calculator', IRANK_CC_URL . 'assets/js/frontend.calculator.js', array(), $v('assets/js/frontend.calculator.js'), true );
    wp_register_script( 'irank-cc-frontend-cards', IRANK_CC_URL . 'assets/js/frontend.cards.js', array(), $v('assets/js/frontend.cards.js'), true );
}
add_action( 'init', 'irank_cc_register_assets' );

function irank_cc_register_blocks() {
    register_block_type( 'irank/weight-loss-calculator', array(
        'api_version' => 2,
        'editor_script' => 'irank-cc-editor-calculator',
        'editor_style' => 'irank-cc-editor',
        'style' => 'irank-cc-frontend',
        'render_callback' => 'irank_cc_render_calculator_block',
        'attributes' => array(
            'minWeight' => array( 'type' => 'number', 'default' => 100 ),
            'maxWeight' => array( 'type' => 'number', 'default' => 400 ),
            'step' => array( 'type' => 'number', 'default' => 1 ),
            'initialWeight' => array( 'type' => 'number', 'default' => 200 ),
            'lossFactor' => array( 'type' => 'number', 'default' => 0.15 ),
            'unit' => array( 'type' => 'string', 'default' => 'lbs' ),
            'beforeImageId' => array( 'type' => 'number', 'default' => 0 ),
            'beforeImage' => array( 'type' => 'string', 'default' => '' ),
            'afterImageId' => array( 'type' => 'number', 'default' => 0 ),
            'afterImage' => array( 'type' => 'string', 'default' => '' ),
            'ctaText' => array( 'type' => 'string', 'default' => 'Get started' ),
            'showTimer' => array( 'type' => 'boolean', 'default' => true ),
            'timerText' => array( 'type' => 'string', 'default' => 'Get pre-approved in under 90 seconds!' ),
            'gradientStart' => array( 'type' => 'string', 'default' => '#FFBB8E' ),
            'gradientEnd' => array( 'type' => 'string', 'default' => '#f67a51' ),
            // Editable texts
            'questionText' => array( 'type' => 'string', 'default' => 'How much weight can you lose' ),
            'weightLabel' => array( 'type' => 'string', 'default' => 'My current weight:' ),
            'lossLabel' => array( 'type' => 'string', 'default' => 'Weight loss potential:' ),
            'beforeLabel' => array( 'type' => 'string', 'default' => 'Before' ),
            'afterLabel' => array( 'type' => 'string', 'default' => 'After' ),
            // Typography: defaults follow Figma spec
            'questionFontFamily' => array( 'type' => 'string', 'default' => 'Nohemi' ),
            'questionFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'questionFontSize'   => array( 'type' => 'string', 'default' => '48px' ),
            'questionLineHeight' => array( 'type' => 'string', 'default' => '54px' ),
            'questionColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'weightFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'weightFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'weightFontSize'   => array( 'type' => 'string', 'default' => '14px' ),
            'weightColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'lossFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'lossFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'lossFontSize'   => array( 'type' => 'string', 'default' => '14px' ),
            'lossColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'beforeFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'beforeFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'beforeFontSize'   => array( 'type' => 'string', 'default' => '12px' ),
            'beforeColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'afterFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'afterFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'afterFontSize'   => array( 'type' => 'string', 'default' => '12px' ),
            'afterColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'ctaFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'ctaFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'ctaFontSize'   => array( 'type' => 'string', 'default' => '18px' ),
            'ctaColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'timerFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'timerFontWeight' => array( 'type' => 'number', 'default' => 500 ),
            'timerFontSize'   => array( 'type' => 'string', 'default' => '14px' ),
            'timerColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            // Button colors (CTA)
            'ctaBg'           => array( 'type' => 'string', 'default' => '#92245A' ),
            'ctaColor'        => array( 'type' => 'string', 'default' => '#ffffff' ),
            'ctaHoverBg'      => array( 'type' => 'string', 'default' => '#ffffff' ),
            'ctaHoverColor'   => array( 'type' => 'string', 'default' => '#000000' ),
            'ctaHoverBorder'  => array( 'type' => 'string', 'default' => '#000000' ),

            // Label colors (Before/After badges)
            'labelBg'           => array( 'type' => 'string', 'default' => '#92245A' ),
            'labelColor'        => array( 'type' => 'string', 'default' => '#ffffff' ),
            'labelHoverBg'      => array( 'type' => 'string', 'default' => '#ffffff' ),
            'labelHoverColor'   => array( 'type' => 'string', 'default' => '#000000' ),
            'labelHoverBorder'  => array( 'type' => 'string', 'default' => '#000000' ),
        ),
    ) );

    register_block_type( 'irank/product-cards', array(
        'api_version' => 2,
        'editor_script' => 'irank-cc-editor-cards',
        'editor_style' => 'irank-cc-editor',
        'style' => 'irank-cc-frontend',
        'render_callback' => 'irank_cc_render_cards_block',
        'attributes' => array(
            'cards' => array( 'type' => 'array', 'default' => array() ),
            // Section texts
            'sectionHeader' => array( 'type' => 'string', 'default' => 'Choose your path to transformation' ),
            'sectionHeading' => array( 'type' => 'string', 'default' => 'All medications included in price.' ),
            'sectionSubheading' => array( 'type' => 'string', 'default' => 'No hidden pharmacy or lab fees.' ),
            'cardsBgStart' => array( 'type' => 'string', 'default' => '#92245A' ),
            'cardsBgEnd'   => array( 'type' => 'string', 'default' => '#92245A' ),
            'cardBg'       => array( 'type' => 'string', 'default' => '#ffffff' ),
            // Card content typography (defaults per spec)
            'nameFontFamily'   => array( 'type' => 'string', 'default' => 'Poppins' ),
            'nameFontWeight'   => array( 'type' => 'number', 'default' => 700 ), // Bold
            'nameFontSize'     => array( 'type' => 'string', 'default' => '36px' ),
            'nameLineHeight'   => array( 'type' => 'string', 'default' => '40px' ),
            'nameColor'        => array( 'type' => 'string', 'default' => '#3B3B3A' ),

            'taglineFontFamily'=> array( 'type' => 'string', 'default' => 'Poppins' ),
            'taglineFontWeight'=> array( 'type' => 'number', 'default' => 600 ), // SemiBold
            'taglineFontSize'  => array( 'type' => 'string', 'default' => '16px' ),
            'taglineLineHeight'=> array( 'type' => 'string', 'default' => '22px' ),
            'taglineColor'     => array( 'type' => 'string', 'default' => '#3B3B3A' ),

            'priceFontFamily'  => array( 'type' => 'string', 'default' => 'Poppins' ),
            'priceFontWeight'  => array( 'type' => 'number', 'default' => 700 ), // Bold
            'priceFontSize'    => array( 'type' => 'string', 'default' => '56px' ),
            'priceLineHeight'  => array( 'type' => 'string', 'default' => '56px' ),
            'priceColor'       => array( 'type' => 'string', 'default' => '#3B3B3A' ),

            'suffixFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'suffixFontWeight' => array( 'type' => 'number', 'default' => 400 ), // Regular
            'suffixFontSize'   => array( 'type' => 'string', 'default' => '16px' ),
            'suffixLineHeight' => array( 'type' => 'string', 'default' => '22px' ),
            'suffixColor'      => array( 'type' => 'string', 'default' => '#3B3B3A' ),

            'priceNoteFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'priceNoteFontWeight' => array( 'type' => 'number', 'default' => 400 ), // Regular
            'priceNoteFontSize'   => array( 'type' => 'string', 'default' => '14px' ),
            'priceNoteLineHeight' => array( 'type' => 'string', 'default' => '16px' ),
            'priceNoteColor'      => array( 'type' => 'string', 'default' => '#3B3B3A' ),

            'benefitsFontFamily'=> array( 'type' => 'string', 'default' => 'Poppins' ),
            'benefitsFontWeight'=> array( 'type' => 'number', 'default' => 600 ), // SemiBold
            'benefitsFontSize'  => array( 'type' => 'string', 'default' => '16px' ),
            'benefitsLineHeight'=> array( 'type' => 'string', 'default' => '22px' ),
            'benefitsColor'     => array( 'type' => 'string', 'default' => '#3B3B3A' ),
            // Legacy fallback (read-only): kept for existing content that used benefitColor
            'benefitColor'      => array( 'type' => 'string', 'default' => '#3B3B3A' ),
            'ctaGradStart'   => array( 'type' => 'string', 'default' => '#E22797' ),
            'ctaGradEnd'     => array( 'type' => 'string', 'default' => '#FD9651' ),
            'ctaColor'       => array( 'type' => 'string', 'default' => '#ffffff' ),
            'ctaHoverGradStart'=> array( 'type' => 'string', 'default' => '#FFB0D6' ),
            'ctaHoverGradEnd'=> array( 'type' => 'string', 'default' => '#FFFFFF' ),
            'ctaHoverColor'  => array( 'type' => 'string', 'default' => '#000000' ),
            'ctaHoverBorder' => array( 'type' => 'string', 'default' => '#000000' ),
            'badgeColor'     => array( 'type' => 'string', 'default' => '#000000' ),
            'badgeGradStart' => array( 'type' => 'string', 'default' => '#FD9651' ),
            'badgeGradEnd'   => array( 'type' => 'string', 'default' => '#F0532C' ),
            // Typography for section header/heading/subheading
            'kickerFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'kickerFontWeight' => array( 'type' => 'number', 'default' => 500 ),
            'kickerFontSize'   => array( 'type' => 'string', 'default' => '14px' ),
            'kickerColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),
            'kickerBorderColor'=> array( 'type' => 'string', 'default' => '#ffffff' ),

            'headingFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'headingFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'headingFontSize'   => array( 'type' => 'string', 'default' => '48px' ),
            'headingLineHeight' => array( 'type' => 'string', 'default' => '54px' ),
            'headingColor'      => array( 'type' => 'string', 'default' => '#ffffff' ),

            'subFontFamily' => array( 'type' => 'string', 'default' => 'Poppins' ),
            'subFontWeight' => array( 'type' => 'number', 'default' => 600 ),
            'subFontSize'   => array( 'type' => 'string', 'default' => '48px' ),
            'subColor'      => array( 'type' => 'string', 'default' => '#FFBB8E' ),
        ),
    ) );
}
add_action( 'init', 'irank_cc_register_blocks' );

function irank_cc_calc_options_merge( $attrs ) {
    $opts = get_option( 'irank_cc_options', irank_cc_default_options() );
    $attrs['minWeight'] = isset($attrs['minWeight']) ? $attrs['minWeight'] : (int)$opts['min_weight'];
    $attrs['maxWeight'] = isset($attrs['maxWeight']) ? $attrs['maxWeight'] : (int)$opts['max_weight'];
    $attrs['step'] = isset($attrs['step']) ? $attrs['step'] : (int)$opts['step'];
    $attrs['lossFactor'] = isset($attrs['lossFactor']) ? $attrs['lossFactor'] : (float)$opts['loss_factor'];
    $attrs['unit'] = isset($attrs['unit']) ? $attrs['unit'] : sanitize_text_field($opts['unit']);
    $attrs['gradientStart'] = isset($attrs['gradientStart']) ? $attrs['gradientStart'] : sanitize_hex_color($opts['gradient_start']);
    $attrs['gradientEnd'] = isset($attrs['gradientEnd']) ? $attrs['gradientEnd'] : sanitize_hex_color($opts['gradient_end']);
    return $attrs;
}

function irank_cc_render_calculator_block( $attributes ) {
    $a = irank_cc_calc_options_merge( $attributes );
    wp_enqueue_style( 'irank-cc-frontend' );
    wp_enqueue_script( 'irank-cc-frontend-calculator' );
    // Localize AJAX URL and nonce for lead submissions (non-REST)
    if ( ! wp_script_is( 'irank-cc-frontend-calculator', 'done' ) ) {
        wp_localize_script( 'irank-cc-frontend-calculator', 'irankCC', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'irank_cc_lead' ),
        ) );
    }

    $min = (int)$a['minWeight'];
    $max = (int)$a['maxWeight'];
    $step = (int)$a['step'];
    $init = isset($a['initialWeight']) ? (int)$a['initialWeight'] : $min;
    $lossFactor = (float)$a['lossFactor'];
    $unit = esc_attr( $a['unit'] );
    $beforeId = isset($a['beforeImageId']) ? intval($a['beforeImageId']) : 0;
    $afterId  = isset($a['afterImageId'])  ? intval($a['afterImageId'])  : 0;
    $before = esc_url( isset($a['beforeImage']) ? $a['beforeImage'] : '' );
    $after = esc_url( isset($a['afterImage']) ? $a['afterImage'] : '' );
    $cta = esc_html( $a['ctaText'] );
    $timer = ! empty( $a['showTimer'] );
    $timerText = esc_html( $a['timerText'] );
    $gs = esc_attr( $a['gradientStart'] );
    $ge = esc_attr( $a['gradientEnd'] );
    $questionText = isset($a['questionText']) ? esc_html($a['questionText']) : 'How much weight can you lose';
    $weightLabel = isset($a['weightLabel']) ? esc_html($a['weightLabel']) : 'My current weight:';
    $lossLabel = isset($a['lossLabel']) ? esc_html($a['lossLabel']) : 'Weight loss potential:';
    $beforeLabel = isset($a['beforeLabel']) ? esc_html($a['beforeLabel']) : 'Before';
    $afterLabel = isset($a['afterLabel']) ? esc_html($a['afterLabel']) : 'After';

    // Button/label colors
    $ctaBg = isset($a['ctaBg']) ? sanitize_hex_color($a['ctaBg']) : '#92245A';
    $ctaTextCol = isset($a['ctaColor']) ? sanitize_hex_color($a['ctaColor']) : '#ffffff';
    $ctaHoverBg = isset($a['ctaHoverBg']) ? sanitize_hex_color($a['ctaHoverBg']) : '#ffffff';
    $ctaHoverCol = isset($a['ctaHoverColor']) ? sanitize_hex_color($a['ctaHoverColor']) : '#000000';
    $ctaHoverBorder = isset($a['ctaHoverBorder']) ? sanitize_hex_color($a['ctaHoverBorder']) : '#000000';

    $labelBg = isset($a['labelBg']) ? sanitize_hex_color($a['labelBg']) : '#92245A';
    $labelCol = isset($a['labelColor']) ? sanitize_hex_color($a['labelColor']) : '#ffffff';
    $labelHoverBg = isset($a['labelHoverBg']) ? sanitize_hex_color($a['labelHoverBg']) : '#ffffff';
    $labelHoverCol = isset($a['labelHoverColor']) ? sanitize_hex_color($a['labelHoverColor']) : '#000000';
    $labelHoverBorder = isset($a['labelHoverBorder']) ? sanitize_hex_color($a['labelHoverBorder']) : '#000000';

    // Typography helpers
    $ff = function($primary){
        $primary = trim((string)$primary);
        if ($primary === 'Nohemi') return "'Nohemi','Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif";
        return "'Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif";
    };
    $style_question = sprintf('font-family:%s;font-weight:%d;font-size:%s;line-height:%s;color:%s;text-align:center;', esc_attr($ff($a['questionFontFamily'])), (int)$a['questionFontWeight'], esc_attr($a['questionFontSize']), esc_attr($a['questionLineHeight']), esc_attr($a['questionColor']) );
    $style_weight   = sprintf('font-family:%s;font-weight:%d;font-size:%s;color:%s;', esc_attr($ff($a['weightFontFamily'])), (int)$a['weightFontWeight'], esc_attr($a['weightFontSize']), esc_attr($a['weightColor']) );
    $style_loss     = sprintf('font-family:%s;font-weight:%d;font-size:%s;color:%s;', esc_attr($ff($a['lossFontFamily'])), (int)$a['lossFontWeight'], esc_attr($a['lossFontSize']), esc_attr($a['lossColor']) );
    // For labels and CTA, omit inline color so CSS variables + :hover can control text color
    $style_before   = sprintf('font-family:%s;font-weight:%d;font-size:%s;', esc_attr($ff($a['beforeFontFamily'])), (int)$a['beforeFontWeight'], esc_attr($a['beforeFontSize']) );
    $style_after    = sprintf('font-family:%s;font-weight:%d;font-size:%s;', esc_attr($ff($a['afterFontFamily'])), (int)$a['afterFontWeight'], esc_attr($a['afterFontSize']) );
    $style_cta      = sprintf('font-family:%s;font-weight:%d;font-size:%s;', esc_attr($ff($a['ctaFontFamily'])), (int)$a['ctaFontWeight'], esc_attr($a['ctaFontSize']) );
    $style_timer    = sprintf('font-family:%s;font-weight:%d;font-size:%s;color:%s;', esc_attr($ff($a['timerFontFamily'])), (int)$a['timerFontWeight'], esc_attr($a['timerFontSize']), esc_attr($a['timerColor']) );

    ob_start();
    ?>
    <section class="irank-calc" data-unit="<?php echo $unit; ?>" data-loss-factor="<?php echo esc_attr($lossFactor); ?>" data-page-id="<?php echo esc_attr( get_queried_object_id() ); ?>" style="--irank-grad-start: <?php echo $gs; ?>; --irank-grad-end: <?php echo $ge; ?>; --irank-cta-bg: <?php echo esc_attr($ctaBg); ?>; --irank-cta-color: <?php echo esc_attr($ctaTextCol); ?>; --irank-cta-hover-bg: <?php echo esc_attr($ctaHoverBg); ?>; --irank-cta-hover-color: <?php echo esc_attr($ctaHoverCol); ?>; --irank-cta-hover-border: <?php echo esc_attr($ctaHoverBorder); ?>; --irank-label-bg: <?php echo esc_attr($labelBg); ?>; --irank-label-color: <?php echo esc_attr($labelCol); ?>; --irank-label-hover-bg: <?php echo esc_attr($labelHoverBg); ?>; --irank-label-hover-color: <?php echo esc_attr($labelHoverCol); ?>; --irank-label-hover-border: <?php echo esc_attr($labelHoverBorder); ?>;">
      <div class="irank-calc__wrap">
        <div class="irank-calc__visual">
          <div class="irank-calc__ba">
            <?php if ( $beforeId ) {
                echo wp_get_attachment_image( $beforeId, 'large', false, array('class'=>'irank-calc__ba--before','decoding'=>'async','loading'=>'eager') );
            } elseif ( $before ) { ?>
                <img src="<?php echo $before; ?>" alt="" decoding="async" class="irank-calc__ba--before" />
            <?php }
            if ( $afterId ) {
                echo wp_get_attachment_image( $afterId, 'large', false, array('class'=>'irank-calc__ba--after','decoding'=>'async','loading'=>'eager') );
            } elseif ( $after ) { ?>
                <img src="<?php echo $after; ?>" alt="" decoding="async" class="irank-calc__ba--after" />
            <?php } ?>
            <div class="irank-calc__ba-handle" tabindex="0" role="slider" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50"></div>
            <span class="irank-calc__label irank-calc__label--before" style="<?php echo $style_before; ?>"><?php echo $beforeLabel; ?></span>
            <span class="irank-calc__label irank-calc__label--after" style="<?php echo $style_after; ?>"><?php echo $afterLabel; ?></span>
          </div>
        </div>
        <div class="irank-calc__panel">
          <div class="irank-calc__question" style="<?php echo $style_question; ?>"><?php echo $questionText; ?></div>
          <div class="irank-calc__current" style="<?php echo $style_weight; ?>">
            <label style="<?php echo $style_weight; ?>"><?php echo $weightLabel; ?></label>
            <div class="irank-calc__value"><span class="irank-calc__weight"><?php echo (int)$init; ?></span> <span class="irank-calc__unit"><?php echo $unit; ?></span></div>
          </div>
          <input type="range" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" value="<?php echo $init; ?>" class="irank-calc__slider"/>
          <div class="irank-calc__loss">
            <label style="<?php echo $style_loss; ?>"><?php echo $lossLabel; ?></label>
            <div class="irank-calc__loss-value"><span class="irank-calc__loss-val" data-sign="-" aria-live="polite">0</span> <span class="irank-calc__loss-unit"><?php echo $unit; ?></span></div>
          </div>
          <button type="button" class="irank-calc__cta" style="<?php echo $style_cta; ?>"><?php echo $cta; ?></button>
          <?php if ( $timer ): ?><div class="irank-calc__timer" style="<?php echo $style_timer; ?>"><?php echo $timerText; ?></div><?php endif; ?>
        </div>
      </div>
      <div class="irank-calc__overlay" hidden aria-hidden="true">
        <div class="irank-calc__overlay-inner" role="dialog" aria-modal="true" aria-label="Get started">
          <button type="button" class="irank-calc__overlay-close" aria-label="Close">×</button>
          <h3>Get started</h3>
          <form class="irank-calc__form" novalidate>
            <div class="irank-calc__field">
              <label for="irank_full_name">Full Name</label>
              <input type="text" id="irank_full_name" name="full_name" />
            </div>
            <div class="irank-calc__field">
              <label for="irank_email">Email</label>
              <input type="email" id="irank_email" name="email" required />
            </div>
            <div class="irank-calc__field">
              <label for="irank_phone">Phone number (with country code)</label>
              <input type="tel" id="irank_phone" name="phone" />
            </div>
            <div class="irank-calc__form-actions">
              <button type="submit" class="irank-calc__overlay-cta">Submit</button>
            </div>
            <div class="irank-calc__form-result" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

// AJAX: Save lead without REST
function irank_cc_ajax_lead(){
    // Optional nonce validation (only if provided)
    $nonce = isset($_POST['nonce']) ? sanitize_text_field( wp_unslash($_POST['nonce']) ) : '';
    if ( $nonce && ! wp_verify_nonce( $nonce, 'irank_cc_lead' ) ) {
        wp_send_json_error( array( 'error' => 'bad_nonce' ), 400 );
    }

    $full_name = isset($_POST['full_name']) ? sanitize_text_field( wp_unslash($_POST['full_name']) ) : '';
    $email     = isset($_POST['email']) ? sanitize_email( wp_unslash($_POST['email']) ) : '';
    $phone     = isset($_POST['phone']) ? sanitize_text_field( wp_unslash($_POST['phone']) ) : '';
    $weight    = isset($_POST['weight']) ? floatval( $_POST['weight'] ) : null;
    $loss      = isset($_POST['loss']) ? floatval( $_POST['loss'] ) : null;
    $page_id   = isset($_POST['page_id']) ? intval( $_POST['page_id'] ) : null;
    $session_id= isset($_POST['session_id']) ? sanitize_text_field( substr( wp_unslash($_POST['session_id']), 0, 64 ) ) : '';
    $referrer  = isset($_POST['referrer']) ? wp_unslash( $_POST['referrer'] ) : '';

    if ( empty($email) || ! is_email($email) ) {
        wp_send_json_error( array( 'error' => 'invalid_email' ), 400 );
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip_hash = hash( 'sha256', 'irank-salt|' . $ip );
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    global $wpdb; $table = $wpdb->prefix . 'irank_calc_leads';
    $wpdb->insert( $table, array(
        'created_at' => current_time('mysql'),
        'page_id'    => $page_id ?: null,
        'full_name'  => $full_name ?: null,
        'email'      => $email ?: null,
        'phone'      => $phone ?: null,
        'weight'     => $weight ?: null,
        'loss'       => $loss ?: null,
        'session_id' => $session_id ?: null,
        'referrer'   => $referrer ?: null,
        'user_agent' => $ua ?: null,
        'ip_hash'    => $ip_hash,
    ), array('%s','%d','%s','%s','%s','%f','%f','%s','%s','%s','%s') );

    wp_send_json_success();
}
add_action('wp_ajax_irank_cc_lead','irank_cc_ajax_lead');
add_action('wp_ajax_nopriv_irank_cc_lead','irank_cc_ajax_lead');

function irank_cc_render_cards_block( $attributes ) {
    wp_enqueue_style( 'irank-cc-frontend' );
    wp_enqueue_style( 'irank-cc-cards' );
    wp_enqueue_script( 'irank-cc-frontend-cards' );
    $cards = isset($attributes['cards']) && is_array($attributes['cards']) ? $attributes['cards'] : array();
    if ( empty( $cards ) ) {
        $cards = array(
            array('name'=>'Compounded Semaglutide','tagline'=>'All meds included','price'=>'$179','priceSuffix'=>'/month','priceNote'=>'(everything included)','benefits'=>array('Free shipping','Weekly support','Cancel anytime'),'badge'=>'Most Popular','ctaText'=>'Get started','ctaUrl'=>'#'),
            array('name'=>'Starter Plan','tagline'=>'Great value','price'=>'$99','priceSuffix'=>'/month','priceNote'=>'','benefits'=>array('Email support','Basic features'),'badge'=>'','ctaText'=>'Choose plan','ctaUrl'=>'#'),
            array('name'=>'Premium','tagline'=>'Everything included','price'=>'$249','priceSuffix'=>'/month','priceNote'=>'','benefits'=>array('Priority support','Extra features'),'badge'=>'','ctaText'=>'Choose plan','ctaUrl'=>'#'),
        );
    }

    // Read styling attributes with fallbacks
    $cardsBgStart = isset($attributes['cardsBgStart']) ? sanitize_hex_color($attributes['cardsBgStart']) : '#92245A';
    $cardsBgEnd   = isset($attributes['cardsBgEnd'])   ? sanitize_hex_color($attributes['cardsBgEnd'])   : '#92245A';
    $cardBg       = isset($attributes['cardBg'])       ? sanitize_hex_color($attributes['cardBg'])       : '#ffffff';
    $tmp_cta_gs   = isset($attributes['ctaGradStart']) ? sanitize_hex_color($attributes['ctaGradStart']) : '';
    $ctaGradStart = $tmp_cta_gs ? $tmp_cta_gs : '#E22797';
    $tmp_cta_ge   = isset($attributes['ctaGradEnd'])   ? sanitize_hex_color($attributes['ctaGradEnd'])   : '';
    $ctaGradEnd   = $tmp_cta_ge ? $tmp_cta_ge : '#FD9651';
    $ctaColor     = isset($attributes['ctaColor'])     ? sanitize_hex_color($attributes['ctaColor'])     : '#ffffff';
    $tmp_cta_hgs  = isset($attributes['ctaHoverGradStart']) ? sanitize_hex_color($attributes['ctaHoverGradStart']) : '';
    $ctaHoverGradStart = $tmp_cta_hgs ? $tmp_cta_hgs : '#FFB0D6';
    $tmp_cta_hge  = isset($attributes['ctaHoverGradEnd']) ? sanitize_hex_color($attributes['ctaHoverGradEnd']) : '';
    $ctaHoverGradEnd = $tmp_cta_hge ? $tmp_cta_hge : '#FFFFFF';
    $ctaHoverCol  = isset($attributes['ctaHoverColor'])? sanitize_hex_color($attributes['ctaHoverColor']): '#000000';
    $ctaHoverBorder = isset($attributes['ctaHoverBorder'])? sanitize_hex_color($attributes['ctaHoverBorder']) : '#000000';
    $badgeColor   = isset($attributes['badgeColor'])   ? sanitize_hex_color($attributes['badgeColor'])   : '#000000';
    $tmp_bgs = isset($attributes['badgeGradStart']) ? sanitize_hex_color($attributes['badgeGradStart']) : '';
    $badgeGradStart = $tmp_bgs ? $tmp_bgs : '#FD9651';
    $tmp_bge = isset($attributes['badgeGradEnd']) ? sanitize_hex_color($attributes['badgeGradEnd']) : '';
    $badgeGradEnd   = $tmp_bge ? $tmp_bge : '#F0532C';

    $ff = function($primary){
        $primary = trim((string)$primary);
        if ($primary === 'Nohemi') return "'Nohemi','Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif";
        return "'Poppins',system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif";
    };
    // Name
    $nameFamily = isset($attributes['nameFontFamily']) ? $ff($attributes['nameFontFamily']) : $ff('Poppins');
    $nameWeight = isset($attributes['nameFontWeight']) ? intval($attributes['nameFontWeight']) : 700;
    $nameSize   = isset($attributes['nameFontSize'])   ? esc_attr($attributes['nameFontSize']) : '36px';
    $nameLine   = isset($attributes['nameLineHeight']) ? esc_attr($attributes['nameLineHeight']) : '40px';
    $nameColor  = isset($attributes['nameColor'])      ? sanitize_hex_color($attributes['nameColor']) : '#3B3B3A';
    // Tagline
    $tagFamily  = isset($attributes['taglineFontFamily']) ? $ff($attributes['taglineFontFamily']) : $ff('Poppins');
    $tagWeight  = isset($attributes['taglineFontWeight']) ? intval($attributes['taglineFontWeight']) : 600;
    $tagSize    = isset($attributes['taglineFontSize'])    ? esc_attr($attributes['taglineFontSize']) : '16px';
    $tagLine    = isset($attributes['taglineLineHeight'])  ? esc_attr($attributes['taglineLineHeight']) : '22px';
    $tagColor   = isset($attributes['taglineColor'])       ? sanitize_hex_color($attributes['taglineColor']) : '#3B3B3A';
    // Price
    $priceFamily= isset($attributes['priceFontFamily']) ? $ff($attributes['priceFontFamily']) : $ff('Poppins');
    $priceWeight= isset($attributes['priceFontWeight']) ? intval($attributes['priceFontWeight']) : 700;
    $priceSize  = isset($attributes['priceFontSize'])   ? esc_attr($attributes['priceFontSize']) : '56px';
    $priceLine  = isset($attributes['priceLineHeight']) ? esc_attr($attributes['priceLineHeight']) : '56px';
    $priceColor = isset($attributes['priceColor'])      ? sanitize_hex_color($attributes['priceColor']) : '#3B3B3A';
    // Suffix
    $sufFamily  = isset($attributes['suffixFontFamily']) ? $ff($attributes['suffixFontFamily']) : $ff('Poppins');
    $sufWeight  = isset($attributes['suffixFontWeight']) ? intval($attributes['suffixFontWeight']) : 400;
    $sufSize    = isset($attributes['suffixFontSize'])   ? esc_attr($attributes['suffixFontSize']) : '16px';
    $sufLine    = isset($attributes['suffixLineHeight']) ? esc_attr($attributes['suffixLineHeight']) : '22px';
    $sufColor   = isset($attributes['suffixColor'])      ? sanitize_hex_color($attributes['suffixColor']) : '#3B3B3A';
    // Price note
    $pnFamily   = isset($attributes['priceNoteFontFamily']) ? $ff($attributes['priceNoteFontFamily']) : $ff('Poppins');
    $pnWeight   = isset($attributes['priceNoteFontWeight']) ? intval($attributes['priceNoteFontWeight']) : 400;
    $pnSize     = isset($attributes['priceNoteFontSize'])   ? esc_attr($attributes['priceNoteFontSize']) : '14px';
    $pnLine     = isset($attributes['priceNoteLineHeight']) ? esc_attr($attributes['priceNoteLineHeight']) : '16px';
    $pnColor    = isset($attributes['priceNoteColor'])      ? sanitize_hex_color($attributes['priceNoteColor']) : '#3B3B3A';
    // Benefits (fallback to legacy benefitColor)
    $bfFamily   = isset($attributes['benefitsFontFamily']) ? $ff($attributes['benefitsFontFamily']) : $ff('Poppins');
    $bfWeight   = isset($attributes['benefitsFontWeight']) ? intval($attributes['benefitsFontWeight']) : 600;
    $bfSize     = isset($attributes['benefitsFontSize'])   ? esc_attr($attributes['benefitsFontSize']) : '16px';
    $bfLine     = isset($attributes['benefitsLineHeight']) ? esc_attr($attributes['benefitsLineHeight']) : '22px';
    $benefitColLegacy = isset($attributes['benefitColor']) ? sanitize_hex_color($attributes['benefitColor']) : '#3B3B3A';
    $bfColor    = isset($attributes['benefitsColor']) ? sanitize_hex_color($attributes['benefitsColor']) : $benefitColLegacy;

    $section_style = sprintf('--cards-grad-start:%s;--cards-grad-end:%s;--card-bg:%s;--cards-cta-grad-start:%s;--cards-cta-grad-end:%s;--cards-cta-color:%s;--cards-cta-hover-grad-start:%s;--cards-cta-hover-grad-end:%s;--cards-cta-hover-color:%s;--cards-cta-hover-border:%s;--badge-color:%s;--badge-grad-start:%s;--badge-grad-end:%s;',
        esc_attr($cardsBgStart), esc_attr($cardsBgEnd), esc_attr($cardBg), esc_attr($ctaGradStart), esc_attr($ctaGradEnd), esc_attr($ctaColor), esc_attr($ctaHoverGradStart), esc_attr($ctaHoverGradEnd), esc_attr($ctaHoverCol), esc_attr($ctaHoverBorder), esc_attr($badgeColor), esc_attr($badgeGradStart), esc_attr($badgeGradEnd)
    );

    // Typography for section texts
    $kFamily = isset($attributes['kickerFontFamily']) ? $ff($attributes['kickerFontFamily']) : $ff('Poppins');
    $kWeight = isset($attributes['kickerFontWeight']) ? intval($attributes['kickerFontWeight']) : 500;
    $kSize   = isset($attributes['kickerFontSize'])   ? esc_attr($attributes['kickerFontSize']) : '14px';
    $kColor  = isset($attributes['kickerColor'])      ? sanitize_hex_color($attributes['kickerColor']) : '#ffffff';
    $kBorder = isset($attributes['kickerBorderColor'])? sanitize_hex_color($attributes['kickerBorderColor']) : '#ffffff';

    $hFamily = isset($attributes['headingFontFamily']) ? $ff($attributes['headingFontFamily']) : $ff('Poppins');
    $hWeight = isset($attributes['headingFontWeight']) ? intval($attributes['headingFontWeight']) : 600;
    $hSize   = isset($attributes['headingFontSize'])   ? esc_attr($attributes['headingFontSize']) : '48px';
    $hLine   = isset($attributes['headingLineHeight']) ? esc_attr($attributes['headingLineHeight']) : '54px';
    $hColor  = isset($attributes['headingColor'])      ? sanitize_hex_color($attributes['headingColor']) : '#ffffff';

    $sFamily = isset($attributes['subFontFamily']) ? $ff($attributes['subFontFamily']) : $ff('Poppins');
    $sWeight = isset($attributes['subFontWeight']) ? intval($attributes['subFontWeight']) : 600;
    $sSize   = isset($attributes['subFontSize'])   ? esc_attr($attributes['subFontSize']) : '48px';
    $sColor  = isset($attributes['subColor'])      ? sanitize_hex_color($attributes['subColor']) : '#FFBB8E';

    ob_start();
    ?>
    <section class="irank-cards" style="<?php echo $section_style; ?>">
      <?php if (!empty($attributes['sectionHeader']) || !empty($attributes['sectionHeading']) || !empty($attributes['sectionSubheading'])): ?>
        <header class="irank-cards__header">
          <?php if (!empty($attributes['sectionHeader'])): ?>
            <div class="irank-cards__kicker" style="font-family:<?php echo $kFamily; ?>;font-weight:<?php echo (int)$kWeight; ?>;font-size:<?php echo $kSize; ?>;color:<?php echo esc_attr($kColor); ?>;border-color:<?php echo esc_attr($kBorder); ?>;">
              <?php echo esc_html($attributes['sectionHeader']); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($attributes['sectionHeading'])): ?><h2 class="irank-cards__heading" style="font-family:<?php echo $hFamily; ?>;font-weight:<?php echo (int)$hWeight; ?>;font-size:<?php echo $hSize; ?>;line-height:<?php echo $hLine; ?>;color:<?php echo esc_attr($hColor); ?>;"><?php echo esc_html($attributes['sectionHeading']); ?></h2><?php endif; ?>
          <?php if (!empty($attributes['sectionSubheading'])): ?><p class="irank-cards__subheading" style="font-family:<?php echo $sFamily; ?>;font-weight:<?php echo (int)$sWeight; ?>;font-size:<?php echo $sSize; ?>;color:<?php echo esc_attr($sColor); ?>;"><?php echo esc_html($attributes['sectionSubheading']); ?></p><?php endif; ?>
        </header>
      <?php endif; ?>
      <div class="irank-cards__track" tabindex="0">
        <?php foreach ($cards as $i => $c): ?>
          <article class="irank-card<?php echo !empty($c['badge']) ? ' is-badged' : ''; ?>">
            <div class="irank-card__media">
              <?php if (!empty($c['imageUrl'])): ?>
                <img src="<?php echo esc_url($c['imageUrl']); ?>" alt="<?php echo esc_attr($c['name']); ?>" />
              <?php endif; ?>
            </div>
            <div class="irank-card__content">
              <?php if (!empty($c['badge'])): ?><div class="irank-card__badge" style="color:<?php echo esc_attr($badgeColor); ?>;"><?php echo esc_html($c['badge']); ?></div><?php endif; ?>
              <h3 class="irank-card__title" style="font-family:<?php echo $nameFamily; ?>;font-weight:<?php echo (int)$nameWeight; ?>;font-size:<?php echo $nameSize; ?>;line-height:<?php echo $nameLine; ?>;color:<?php echo esc_attr($nameColor); ?>;"><?php echo esc_html($c['name']); ?></h3>
              <p class="irank-card__tagline" style="font-family:<?php echo $tagFamily; ?>;font-weight:<?php echo (int)$tagWeight; ?>;font-size:<?php echo $tagSize; ?>;line-height:<?php echo $tagLine; ?>;color:<?php echo esc_attr($tagColor); ?>;"><?php echo esc_html($c['tagline']); ?></p>
              <?php
                $suffix = '/month';
                if ( array_key_exists('priceSuffix', $c) ) {
                    $tmp = trim((string)$c['priceSuffix']);
                    $suffix = ($tmp === '') ? '' : $tmp;
                }
                $note = '';
                if ( ! empty($c['priceNote']) ) { $note = trim((string)$c['priceNote']); }
              ?>
              <div class="irank-card__price" style="font-family:<?php echo $priceFamily; ?>;font-weight:<?php echo (int)$priceWeight; ?>;font-size:<?php echo $priceSize; ?>;line-height:<?php echo $priceLine; ?>;color:<?php echo esc_attr($priceColor); ?>;">
                <?php echo esc_html($c['price']); ?><?php if ($suffix !== ''): ?> <span class="irank-card__price-suffix" style="font-family:<?php echo $sufFamily; ?>;font-weight:<?php echo (int)$sufWeight; ?>;font-size:<?php echo $sufSize; ?>;line-height:<?php echo $sufLine; ?>;color:<?php echo esc_attr($sufColor); ?>;"><?php echo esc_html($suffix); ?></span><?php endif; ?>
              </div>
              <?php if ($note !== ''): ?><p class="irank-card__price-note" style="font-family:<?php echo $pnFamily; ?>;font-weight:<?php echo (int)$pnWeight; ?>;font-size:<?php echo $pnSize; ?>;line-height:<?php echo $pnLine; ?>;color:<?php echo esc_attr($pnColor); ?>;"><?php echo esc_html($note); ?></p><?php endif; ?>
              <ul class="irank-card__benefits" style="font-family:<?php echo $bfFamily; ?>;font-weight:<?php echo (int)$bfWeight; ?>;font-size:<?php echo $bfSize; ?>;line-height:<?php echo $bfLine; ?>;color:<?php echo esc_attr($bfColor); ?>;">
                <?php if (!empty($c['benefits']) && is_array($c['benefits'])) foreach ($c['benefits'] as $b): ?>
                  <li><?php echo esc_html($b); ?></li>
                <?php endforeach; ?>
              </ul>
              <a href="<?php echo esc_url( isset($c['ctaUrl'])?$c['ctaUrl']:'#' ); ?>" class="irank-card__cta"><?php echo esc_html( isset($c['ctaText'])?$c['ctaText']:'Select' ); ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="irank-cards__nav">
        <button type="button" class="irank-cards__prev" aria-label="Previous">‹</button>
        <button type="button" class="irank-cards__next" aria-label="Next">›</button>
      </div>
    </section>
    <?php
    return ob_get_clean();
}