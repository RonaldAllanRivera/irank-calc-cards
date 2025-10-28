<?php
/**
 * Plugin Name: IRANK Calc & Cards
 * Description: Weight loss calculator and product cards as no-build Gutenberg blocks with same-page results and first-party tracking.
 * Version: 0.1.4
 * Author: Ronald Allan Rivera
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
        'tracking_enabled' => 1,
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
            <div class="irank-calc__loss-value"><span class="irank-calc__loss-val" aria-live="polite">0</span> <span class="irank-calc__loss-unit"><?php echo $unit; ?></span></div>
          </div>
          <button type="button" class="irank-calc__cta" style="<?php echo $style_cta; ?>"><?php echo $cta; ?></button>
          <?php if ( $timer ): ?><div class="irank-calc__timer" style="<?php echo $style_timer; ?>"><?php echo $timerText; ?></div><?php endif; ?>
        </div>
      </div>
      <div class="irank-calc__overlay" hidden aria-hidden="true">
        <div class="irank-calc__overlay-inner" role="dialog" aria-modal="true" aria-label="Your result">
          <button type="button" class="irank-calc__overlay-close" aria-label="Close">×</button>
          <h3>Your estimated weight loss</h3>
          <p><span class="irank-calc__res-weight"></span> <?php echo $unit; ?> current weight</p>
          <p>Potential loss: <strong><span class="irank-calc__res-loss"></span> <?php echo $unit; ?></strong></p>
          <a class="irank-calc__overlay-cta" href="#">Continue</a>
        </div>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

function irank_cc_render_cards_block( $attributes ) {
    wp_enqueue_style( 'irank-cc-frontend' );
    wp_enqueue_script( 'irank-cc-frontend-cards' );
    $cards = isset($attributes['cards']) && is_array($attributes['cards']) ? $attributes['cards'] : array();
    if ( empty( $cards ) ) {
        $cards = array(
            array('name'=>'Compounded Semaglutide','tagline'=>'All meds included','price'=>'$179','benefits'=>array('Free shipping','Weekly support','Cancel anytime'),'badge'=>'Most Popular','ctaText'=>'Get started','ctaUrl'=>'#'),
            array('name'=>'Starter Plan','tagline'=>'Great value','price'=>'$99','benefits'=>array('Email support','Basic features'),'badge'=>'','ctaText'=>'Choose plan','ctaUrl'=>'#'),
            array('name'=>'Premium','tagline'=>'Everything included','price'=>'$249','benefits'=>array('Priority support','Extra features'),'badge'=>'','ctaText'=>'Choose plan','ctaUrl'=>'#'),
        );
    }
    ob_start();
    ?>
    <section class="irank-cards">
      <div class="irank-cards__track" tabindex="0">
        <?php foreach ($cards as $i => $c): ?>
          <article class="irank-card<?php echo !empty($c['badge']) ? ' is-badged' : ''; ?>">
            <?php if (!empty($c['badge'])): ?><div class="irank-card__badge"><?php echo esc_html($c['badge']); ?></div><?php endif; ?>
            <h3 class="irank-card__title"><?php echo esc_html($c['name']); ?></h3>
            <p class="irank-card__tagline"><?php echo esc_html($c['tagline']); ?></p>
            <div class="irank-card__price"><?php echo esc_html($c['price']); ?></div>
            <ul class="irank-card__benefits">
              <?php if (!empty($c['benefits']) && is_array($c['benefits'])) foreach ($c['benefits'] as $b): ?>
                <li><?php echo esc_html($b); ?></li>
              <?php endforeach; ?>
            </ul>
            <a href="<?php echo esc_url( isset($c['ctaUrl'])?$c['ctaUrl']:'#' ); ?>" class="irank-card__cta"><?php echo esc_html( isset($c['ctaText'])?$c['ctaText']:'Select' ); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="irank-cards__nav">
        <button type="button" class="irank-cards__prev" aria-label="Previous">‹</button>
        <div class="irank-cards__dots" aria-hidden="true"></div>
        <button type="button" class="irank-cards__next" aria-label="Next">›</button>
      </div>
    </section>
    <?php
    return ob_get_clean();
}

function irank_cc_rest_register() {
    register_rest_route( 'irank/v1', '/track', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => 'irank_cc_rest_track',
        'args' => array(),
    ) );
}
add_action( 'rest_api_init', 'irank_cc_rest_register' );

function irank_cc_rest_track( WP_REST_Request $req ) {
    $opts = get_option( 'irank_cc_options', irank_cc_default_options() );
    if ( empty( $opts['tracking_enabled'] ) ) return new WP_REST_Response( array('ok'=>false), 200 );

    $weight = floatval( $req->get_param('weight') );
    $loss = floatval( $req->get_param('loss') );
    $page_id = intval( $req->get_param('page_id') );
    $session_id = sanitize_text_field( substr( (string)$req->get_param('session_id'), 0, 64 ) );
    $ref = wp_unslash( (string) $req->get_param('referrer') );

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ip_hash = hash( 'sha256', 'irank-salt|' . $ip );
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    global $wpdb;
    $table = $wpdb->prefix . 'irank_calc_events';
    $wpdb->insert( $table, array(
        'created_at' => current_time('mysql'),
        'page_id' => $page_id ?: null,
        'weight' => $weight ?: null,
        'loss' => $loss ?: null,
        'session_id' => $session_id ?: null,
        'referrer' => $ref ?: null,
        'user_agent' => $ua ?: null,
        'ip_hash' => $ip_hash,
        'variant' => '',
    ), array('%s','%d','%f','%f','%s','%s','%s','%s','%s') );

    return new WP_REST_Response( array('ok'=>true), 200 );
}
