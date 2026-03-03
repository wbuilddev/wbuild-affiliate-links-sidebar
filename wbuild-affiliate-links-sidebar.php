<?php
/**
 * Plugin Name: wBuild Affiliate Links Sidebar
 * Description: Auto-detects affiliate links in your content and displays them in a sidebar widget or shortcode.
 * Version: 1.7.0
 * Author: wBuild.dev
 * Author URI: https://wbuild.dev
 * License: GPL-2.0+
 * Text Domain: wbuild-affiliate-links-sidebar
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WBUILD_ALS_VERSION', '1.7.0' );

// ============================================================
// SETTINGS LINK ON PLUGINS PAGE
// ============================================================
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wbuild_als_action_links' );
function wbuild_als_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=wbuild-affiliate-links-sidebar' ) ) . '">' . esc_html__( 'Settings', 'wbuild-affiliate-links-sidebar' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

// ============================================================
// FRONTEND STYLES — properly enqueued (Review Fix #3)
// ============================================================
add_action( 'wp_enqueue_scripts', 'wbuild_als_enqueue_frontend_styles' );
function wbuild_als_enqueue_frontend_styles() {
    if ( ! is_singular() ) {
        return;
    }

    // Default styles
    $default_css = '
        .affiliate-links-widget, .affiliate-links-shortcode {
            margin: 2em 0;
            padding: 1.5em;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
        }
        .affiliate-links-widget h4, .affiliate-links-shortcode h4 {
            margin: 0 0 1em;
            font-size: 1.25em;
            color: #232f3e;
        }
        .affiliate-links-widget ul, .affiliate-links-shortcode ul {
            list-style: none;
            padding: 0;
            margin: 0 0 1em;
        }
        .affiliate-links-widget li, .affiliate-links-shortcode li {
            margin-bottom: 12px;
            padding: 12px 14px;
            background: white;
            border-left: 4px solid #ff9900;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .affiliate-links-widget li:hover, .affiliate-links-shortcode li:hover {
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .affiliate-links-widget a, .affiliate-links-shortcode a {
            color: #232f3e;
            text-decoration: none;
            font-weight: 500;
        }
        .affiliate-links-widget a:hover, .affiliate-links-shortcode a:hover {
            color: #ff9900;
            text-decoration: underline;
        }
        .affiliate-disclosure {
            font-size: 0.85em;
            color: #666;
            text-align: center;
            margin-top: 1em;
            font-style: italic;
        }
        .wbuild-als-credit {
            font-size: 0.75em;
            color: #aaa;
            text-align: center;
            margin-top: 12px;
        }
        @media (max-width: 768px) { .desktop-only { display: none !important; } }
    ';

    wp_register_style( 'wbuild-als-default', false, array(), WBUILD_ALS_VERSION );
    wp_enqueue_style( 'wbuild-als-default' );
    wp_add_inline_style( 'wbuild-als-default', $default_css );

    // Custom user CSS (from settings)
    $settings = get_option( 'wbuild_als_settings', array() );
    $custom_css = trim( ( $settings['sidebar_css'] ?? '' ) . "\n" . ( $settings['shortcode_css'] ?? '' ) );

    if ( $custom_css !== '' ) {
        wp_add_inline_style( 'wbuild-als-default', wp_strip_all_tags( $custom_css ) );
    }
}

// ============================================================
// ADMIN STYLES — properly enqueued (Review Fix #3)
// ============================================================
add_action( 'admin_enqueue_scripts', 'wbuild_als_enqueue_admin_styles' );
function wbuild_als_enqueue_admin_styles( $hook ) {
    if ( 'settings_page_wbuild-affiliate-links-sidebar' !== $hook ) {
        return;
    }

    $admin_css = '
        .dashicons.dashicons-info {
            text-decoration: none;
            color: #666;
            font-size: 16px;
            vertical-align: middle;
            cursor: pointer;
            margin-left: 6px;
        }
        .dashicons.dashicons-info:hover {
            color: #0073aa;
        }
        .info-tooltip {
            cursor: help;
        }
    ';

    wp_register_style( 'wbuild-als-admin', false, array(), WBUILD_ALS_VERSION );
    wp_enqueue_style( 'wbuild-als-admin' );
    wp_add_inline_style( 'wbuild-als-admin', $admin_css );
}

// ============================================================
// SETTINGS PAGE
// ============================================================
add_action( 'admin_menu', 'wbuild_als_add_settings_page' );
function wbuild_als_add_settings_page() {
    add_options_page(
        'wBuild Affiliate Links Sidebar Settings',
        'wBuild Affiliate Sidebar',
        'manage_options',
        'wbuild-affiliate-links-sidebar',
        'wbuild_als_settings_page'
    );
}

function wbuild_als_settings_page() {
    if ( isset( $_POST['wbuild_als_submit'] ) && check_admin_referer( 'wbuild_als_settings_nonce' ) ) {
        $input = wp_unslash( $_POST );

        $settings = array(
            'prefix'                    => esc_url_raw( trim( $input['prefix'] ?? 'https://amzn.to/' ) ),
            'sidebar_css'               => trim( $input['sidebar_css'] ?? '' ),
            'shortcode_css'             => trim( $input['shortcode_css'] ?? '' ),
            'widget_title'              => sanitize_text_field( $input['widget_title'] ?? 'Recommended Products on Page' ),
            'shortcode_title'           => sanitize_text_field( $input['shortcode_title'] ?? 'Recommended Products on Page' ),
            'disclosure'                => wp_kses_post( $input['disclosure'] ?? '' ),
            'credit_location'           => sanitize_text_field( $input['credit_location'] ?? 'none' ),
            'hide_shortcode_on_desktop' => isset( $input['hide_shortcode_on_desktop'] ) ? 1 : 0,
            'link_new_tab'              => isset( $input['link_new_tab'] ) ? 1 : 0,
            'link_rel_sponsored'        => isset( $input['link_rel_sponsored'] ) ? 1 : 0,
            'link_rel_nofollow'         => isset( $input['link_rel_nofollow'] ) ? 1 : 0,
            'link_rel_noopener'         => isset( $input['link_rel_noopener'] ) ? 1 : 0,
            'max_links_display'         => max( 1, min( 5, (int) ( $input['max_links_display'] ?? 5 ) ) ),
        );
        update_option( 'wbuild_als_settings', $settings );
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'wbuild-affiliate-links-sidebar' ) . '</p></div>';
    }

    $defaults = array(
        'prefix'                    => 'https://amzn.to/',
        'sidebar_css'               => ".affiliate-links-widget { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5em; }\n.affiliate-links-widget li { border-left-color: #ff9900; background: white; }\n.affiliate-links-widget a:hover { color: #ff9900; }",
        'shortcode_css'             => ".affiliate-links-shortcode { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5em; margin: 2em 0; }\n.affiliate-links-shortcode h4 { color: #232f3e; }\n.affiliate-links-shortcode li { border-left-color: #ff9900; background: white; }\n.affiliate-links-shortcode a:hover { color: #ff9900; }",
        'widget_title'              => 'Recommended Products on Page',
        'shortcode_title'           => 'Recommended Products on Page',
        'disclosure'                => 'As an Amazon Associate I earn from qualifying purchases. This site contains affiliate links, commissions may be earned at no extra cost to you.',
        'credit_location'           => 'none',
        'hide_shortcode_on_desktop' => 0,
        'link_new_tab'              => 1,
        'link_rel_sponsored'        => 1,
        'link_rel_nofollow'         => 0,
        'link_rel_noopener'         => 1,
        'max_links_display'         => 5,
    );
    $settings = wp_parse_args( get_option( 'wbuild_als_settings', $defaults ), $defaults );

    $default_widget_css = ".affiliate-links-widget { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5em; }\n.affiliate-links-widget li { border-left-color: #ff9900; background: white; }\n.affiliate-links-widget a:hover { color: #ff9900; }";
    $default_shortcode_css = ".affiliate-links-shortcode { background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; padding: 1.5em; margin: 2em 0; }\n.affiliate-links-shortcode h4 { color: #232f3e; }\n.affiliate-links-shortcode li { border-left-color: #ff9900; background: white; }\n.affiliate-links-shortcode a:hover { color: #ff9900; }";
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'wBuild Affiliate Links Sidebar Settings', 'wbuild-affiliate-links-sidebar' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'wbuild_als_settings_nonce' ); ?>

            <h2><?php esc_html_e( 'Affiliate Prefix', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Link Prefix', 'wbuild-affiliate-links-sidebar' ); ?>
                        <a href="https://wbuild.dev/affiliate-links-sidebar/" target="_blank" rel="noopener noreferrer" class="dashicons dashicons-info" title="<?php esc_attr_e( 'Want to support multiple affiliate programs (e.g., Amazon + ShareASale)? Check out Pro for multiple prefixes.', 'wbuild-affiliate-links-sidebar' ); ?>"></a>
                    </th>
                    <td>
                        <input type="text" name="prefix" value="<?php echo esc_attr( $settings['prefix'] ); ?>" class="regular-text">
                        <p class="description">
                            <?php esc_html_e( 'Used by both widget and shortcode.', 'wbuild-affiliate-links-sidebar' ); ?><br>
                            <?php esc_html_e( 'A prefix is the beginning part of your affiliate links (the domain/shortener before the unique code).', 'wbuild-affiliate-links-sidebar' ); ?><br>
                            <?php esc_html_e( 'The plugin scans your page content for any links that start with this prefix and displays them.', 'wbuild-affiliate-links-sidebar' ); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Titles', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Widget Title', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <input type="text" name="widget_title" value="<?php echo esc_attr( $settings['widget_title'] ); ?>" class="regular-text">
                        <p class="description">
                            <?php esc_html_e( 'Title shown above the list in the sidebar widget.', 'wbuild-affiliate-links-sidebar' ); ?><br>
                            <a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" target="_blank"><?php esc_html_e( 'Add the widget here → Appearance → Widgets', 'wbuild-affiliate-links-sidebar' ); ?></a>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Shortcode Title', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <input type="text" name="shortcode_title" value="<?php echo esc_attr( $settings['shortcode_title'] ); ?>" class="regular-text">
                        <p class="description">
                            <?php
                            printf(
                                /* translators: %s: shortcode tag */
                                esc_html__( 'Title shown above the list when using the shortcode %s in any post or page.', 'wbuild-affiliate-links-sidebar' ),
                                '<code>[affiliate-links]</code>'
                            );
                            ?>
                        </p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Disclosure', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr><th scope="row"><?php esc_html_e( 'Disclosure Text', 'wbuild-affiliate-links-sidebar' ); ?></th><td><textarea name="disclosure" rows="3" class="widefat"><?php echo esc_textarea( $settings['disclosure'] ); ?></textarea></td></tr>
            </table>

            <h2><?php esc_html_e( 'Link Behavior', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Behavior', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <label><input type="checkbox" name="link_new_tab" value="1" <?php checked( $settings['link_new_tab'], 1 ); ?>> <?php esc_html_e( 'Open links in new tab', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="checkbox" name="link_rel_sponsored" value="1" <?php checked( $settings['link_rel_sponsored'], 1 ); ?>> <?php esc_html_e( 'Add rel="sponsored"', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="checkbox" name="link_rel_nofollow" value="1" <?php checked( $settings['link_rel_nofollow'], 1 ); ?>> <?php esc_html_e( 'Add rel="nofollow"', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="checkbox" name="link_rel_noopener" value="1" <?php checked( $settings['link_rel_noopener'], 1 ); ?>> <?php esc_html_e( 'Add rel="noopener" (when new tab)', 'wbuild-affiliate-links-sidebar' ); ?></label>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Display Limits', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php esc_html_e( 'Max Links to Display', 'wbuild-affiliate-links-sidebar' ); ?>
                        <span class="dashicons dashicons-info info-tooltip" title="<?php esc_attr_e( 'The free version is limited to a maximum of 5 links per page. The Pro version removes this limit and allows unlimited links.', 'wbuild-affiliate-links-sidebar' ); ?>"></span>
                    </th>
                    <td>
                        <select name="max_links_display">
                            <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                                <option value="<?php echo esc_attr( $i ); ?>"<?php selected( $settings['max_links_display'], $i ); ?>><?php echo esc_html( $i ); ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose how many affiliate links to show on this page (limited to 5 in free version).', 'wbuild-affiliate-links-sidebar' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Credit', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Credit Location', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <label><input type="radio" name="credit_location" value="none" <?php checked( $settings['credit_location'], 'none' ); ?>> <?php esc_html_e( 'Never', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="radio" name="credit_location" value="sidebar" <?php checked( $settings['credit_location'], 'sidebar' ); ?>> <?php esc_html_e( 'Sidebar widget only', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="radio" name="credit_location" value="shortcode" <?php checked( $settings['credit_location'], 'shortcode' ); ?>> <?php esc_html_e( 'Shortcode block only', 'wbuild-affiliate-links-sidebar' ); ?></label><br>
                        <label><input type="radio" name="credit_location" value="both" <?php checked( $settings['credit_location'], 'both' ); ?>> <?php esc_html_e( 'Both', 'wbuild-affiliate-links-sidebar' ); ?></label>
                        <p class="description"><?php esc_html_e( 'Shows a small "Powered by wBuild.dev" credit linking to the plugin page. Opt-in only. Defaults to never.', 'wbuild-affiliate-links-sidebar' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Shortcode Visibility', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Hide shortcode on desktop', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="hide_shortcode_on_desktop" value="1" <?php checked( $settings['hide_shortcode_on_desktop'], 1 ); ?>>
                            <?php esc_html_e( 'Hide shortcode block completely on desktop (show only on mobile/tablet)', 'wbuild-affiliate-links-sidebar' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'Useful when using sidebar widget on desktop and shortcode on mobile.', 'wbuild-affiliate-links-sidebar' ); ?></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Custom CSS', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Widget CSS', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <textarea name="sidebar_css" rows="8" class="widefat"><?php echo esc_textarea( $settings['sidebar_css'] ?: $default_widget_css ); ?></textarea>
                        <p><button type="button" class="button" onclick="document.querySelector('[name=\'sidebar_css\']').value = '<?php echo esc_js( addslashes( $default_widget_css ) ); ?>';"><?php esc_html_e( 'Reset to Default', 'wbuild-affiliate-links-sidebar' ); ?></button></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Shortcode CSS', 'wbuild-affiliate-links-sidebar' ); ?></th>
                    <td>
                        <textarea name="shortcode_css" rows="8" class="widefat"><?php echo esc_textarea( $settings['shortcode_css'] ?: $default_shortcode_css ); ?></textarea>
                        <p><button type="button" class="button" onclick="document.querySelector('[name=\'shortcode_css\']').value = '<?php echo esc_js( addslashes( $default_shortcode_css ) ); ?>';"><?php esc_html_e( 'Reset to Default', 'wbuild-affiliate-links-sidebar' ); ?></button></p>
                    </td>
                </tr>
            </table>

            <h2><?php esc_html_e( 'Like this plugin?', 'wbuild-affiliate-links-sidebar' ); ?></h2>
            <p style="font-size: 1.1em;">
                <?php esc_html_e( 'The Pro version adds unlimited links, multiple affiliate programs, and more custom behaviors.', 'wbuild-affiliate-links-sidebar' ); ?><br>
                <a href="https://wbuild.dev/affiliate-links-sidebar/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Pro details →', 'wbuild-affiliate-links-sidebar' ); ?></a>
            </p>

            <?php submit_button( __( 'Save Settings', 'wbuild-affiliate-links-sidebar' ), 'primary', 'wbuild_als_submit' ); ?>
        </form>
    </div>
    <?php
}

// ============================================================
// WIDGET
// ============================================================
class WBuild_Affiliate_Links_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'wbuild_als_widget',
            'wBuild Affiliate Links Sidebar (Free)',
            array( 'description' => __( 'Shows affiliate links from page content using global prefix (limited to 5 in free).', 'wbuild-affiliate-links-sidebar' ) )
        );
    }

    public function widget( $args, $instance ) {
        if ( ! is_singular() ) {
            return;
        }

        if ( ! empty( $instance['desktop_only'] ) && wp_is_mobile() ) {
            return;
        }

        global $post;
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook
        $content = apply_filters( 'the_content', $post->post_content );

        $settings = get_option( 'wbuild_als_settings', array() );
        $prefix = rtrim( $settings['prefix'] ?? 'https://amzn.to', '/' );
        $pattern = '/(' . preg_quote( $prefix, '/' ) . '\/[^\s<>"\']+)/i';
        preg_match_all( $pattern, $content, $matches );
        $links = array_unique( $matches[1] ?? array() );
        if ( empty( $links ) ) {
            return;
        }

        $max_display = ! empty( $settings['max_links_display'] ) ? max( 1, min( 5, (int) $settings['max_links_display'] ) ) : 5;
        $links = array_slice( $links, 0, $max_display );

        $title = ! empty( $instance['title'] ) ? $instance['title'] : ( $settings['widget_title'] ?? 'Recommended Products on Page' );

        $class = 'affiliate-links-widget';
        if ( ! empty( $instance['desktop_only'] ) ) {
            $class .= ' desktop-only';
        }

        $target = ! empty( $settings['link_new_tab'] ) ? ' target="_blank"' : '';
        $rel_parts = array();
        if ( ! empty( $settings['link_rel_sponsored'] ) ) {
            $rel_parts[] = 'sponsored';
        }
        if ( ! empty( $settings['link_rel_nofollow'] ) ) {
            $rel_parts[] = 'nofollow';
        }
        if ( ! empty( $settings['link_rel_noopener'] ) && ! empty( $settings['link_new_tab'] ) ) {
            $rel_parts[] = 'noopener';
        }
        $rel_attr = $rel_parts ? ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"' : '';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core widget args; escaping would break HTML structure
        echo $args['before_widget'];
        echo '<div class="' . esc_attr( $class ) . '">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core widget args; escaping would break HTML structure
        echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        echo '<ul>';
        foreach ( $links as $link ) {
            $text = $this->get_link_text( $content, $link );
            $display = $text ?: str_replace( array( 'https://', 'http://' ), '', $link );
            printf(
                '<li><a href="%s"%s%s>%s</a></li>',
                esc_url( $link ),
                $target ? ' target="' . esc_attr( '_blank' ) . '"' : '',
                $rel_parts ? ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"' : '',
                esc_html( $display )
            );
        }
        echo '</ul>';
        if ( ! empty( $settings['disclosure'] ) ) {
            echo '<p class="affiliate-disclosure">' . wp_kses_post( $settings['disclosure'] ) . '</p>';
        }
        if ( in_array( $settings['credit_location'] ?? 'none', array( 'sidebar', 'both' ), true ) ) {
            echo '<p class="wbuild-als-credit">' . wp_kses( __( 'Powered by <a href="https://wbuild.dev/affiliate-links-sidebar/" target="_blank" rel="noopener noreferrer">wBuild.dev</a>', 'wbuild-affiliate-links-sidebar' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ) . '</p>';
        }
        echo '</div>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core widget args; escaping would break HTML structure
        echo $args['after_widget'];
    }

    private function get_link_text( $content, $link ) {
        $escaped = preg_quote( $link, '/' );
        preg_match( '/<a\s+[^>]*href=["\']' . $escaped . '["\'][^>]*>(.*?)<\/a>/is', $content, $m );
        return ! empty( $m[1] ) ? wp_strip_all_tags( $m[1] ) : '';
    }

    public function form( $instance ) {
        $title = $instance['title'] ?? '';
        $desktop_only = ! empty( $instance['desktop_only'] );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title (overrides global)', 'wbuild-affiliate-links-sidebar' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'desktop_only' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'desktop_only' ) ); ?>" value="1" <?php checked( $desktop_only ); ?>>
            <label for="<?php echo esc_attr( $this->get_field_id( 'desktop_only' ) ); ?>"><?php esc_html_e( 'Show only on desktop (hide on mobile/tablet)', 'wbuild-affiliate-links-sidebar' ); ?></label>
        </p>
        <p style="font-size:0.9em; color:#555;">
            <?php
            printf(
                wp_kses(
                    /* translators: %s: URL to the plugin settings page */
                    __( 'All settings: <a href="%s">Settings → wBuild Affiliate Sidebar</a>', 'wbuild-affiliate-links-sidebar' ),
                    array( 'a' => array( 'href' => array() ) )
                ),
                esc_url( admin_url( 'options-general.php?page=wbuild-affiliate-links-sidebar' ) )
            );
            ?>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        return array(
            'title'        => wp_strip_all_tags( $new_instance['title'] ?? '' ),
            'desktop_only' => ! empty( $new_instance['desktop_only'] ) ? 1 : 0,
        );
    }
}

// ============================================================
// SHORTCODE
// ============================================================
function wbuild_als_shortcode() {
    if ( ! is_singular() ) {
        return '';
    }

    $settings = get_option( 'wbuild_als_settings', array() );

    if ( ! empty( $settings['hide_shortcode_on_desktop'] ) && ! wp_is_mobile() ) {
        return '';
    }

    static $in_progress = false;
    if ( $in_progress ) {
        return '';
    }
    $in_progress = true;

    global $post;
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core WordPress hook
    $content = apply_filters( 'the_content', $post->post_content );

    $prefix = rtrim( $settings['prefix'] ?? 'https://amzn.to', '/' );
    $pattern = '/(' . preg_quote( $prefix, '/' ) . '\/[^\s<>"\']+)/i';
    preg_match_all( $pattern, $content, $matches );
    $links = array_unique( $matches[1] ?? array() );

    if ( empty( $links ) ) {
        $in_progress = false;
        return '';
    }

    $max_display = ! empty( $settings['max_links_display'] ) ? max( 1, min( 5, (int) $settings['max_links_display'] ) ) : 5;
    $links = array_slice( $links, 0, $max_display );

    $title = $settings['shortcode_title'] ?? 'Recommended Products on Page';

    $target = ! empty( $settings['link_new_tab'] ) ? ' target="_blank"' : '';
    $rel_parts = array();
    if ( ! empty( $settings['link_rel_sponsored'] ) ) {
        $rel_parts[] = 'sponsored';
    }
    if ( ! empty( $settings['link_rel_nofollow'] ) ) {
        $rel_parts[] = 'nofollow';
    }
    if ( ! empty( $settings['link_rel_noopener'] ) && ! empty( $settings['link_new_tab'] ) ) {
        $rel_parts[] = 'noopener';
    }
    $rel_attr = $rel_parts ? ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"' : '';

    ob_start();
    ?>
    <div class="affiliate-links-shortcode">
        <h4><?php echo esc_html( $title ); ?></h4>
        <ul>
            <?php foreach ( $links as $link ) :
                $text = '';
                $escaped = preg_quote( $link, '/' );
                preg_match( '/<a\s+[^>]*href=["\']' . $escaped . '["\'][^>]*>(.*?)<\/a>/is', $content, $m );
                if ( ! empty( $m[1] ) ) {
                    $text = wp_strip_all_tags( $m[1] );
                }
                $display = $text ?: str_replace( array( 'https://', 'http://' ), '', $link );
            ?>
                <li><a href="<?php echo esc_url( $link ); ?>"<?php
                    if ( ! empty( $settings['link_new_tab'] ) ) {
                        echo ' target="' . esc_attr( '_blank' ) . '"';
                    }
                    if ( $rel_parts ) {
                        echo ' rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"';
                    }
                ?>><?php echo esc_html( $display ); ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php if ( ! empty( $settings['disclosure'] ) ) : ?>
            <p class="affiliate-disclosure"><?php echo wp_kses_post( $settings['disclosure'] ); ?></p>
        <?php endif; ?>
        <?php if ( in_array( $settings['credit_location'] ?? 'none', array( 'shortcode', 'both' ), true ) ) : ?>
            <p class="wbuild-als-credit"><?php echo wp_kses( __( 'Powered by <a href="https://wbuild.dev/affiliate-links-sidebar/" target="_blank" rel="noopener noreferrer">wBuild.dev</a>', 'wbuild-affiliate-links-sidebar' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ); ?></p>
        <?php endif; ?>
    </div>
    <?php
    $in_progress = false;
    return ob_get_clean();
}
add_shortcode( 'affiliate-links', 'wbuild_als_shortcode' );

// ============================================================
// REGISTER WIDGET
// ============================================================
add_action( 'widgets_init', function () {
    register_widget( 'WBuild_Affiliate_Links_Widget' );
} );
