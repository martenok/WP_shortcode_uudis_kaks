<?php if (!defined('FW')) die('Forbidden');

// find the uri to the shortcode folder
$uri = fw_get_template_customizations_directory_uri('/extensions/shortcodes/shortcodes/uudis-kaks');

wp_enqueue_style(
    'fw-shortcode-uudis-kaks',
    $uri . '/static/css/styles.css'
);

wp_enqueue_script(
    'fw-shortcode-uudis-kaks',
    $uri . '/static/js/scripts.js'
);

wp_enqueue_script(
    'angularjs',
    'https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js'
);

wp_localize_script( 'fw-shortcode-uudis-kaks', 'uudis_kaks_params', array( 'uudis_kaks_ajax_url' => admin_url( 'admin-ajax.php' ) ) );

// if (!function_exists('_action_theme_shortcode_uudis_kaks_enqueue_dynamic_css')):
//
// /**
//  * @internal
//  * @param array $data
//  */
// function _action_theme_shortcode_uudis_kaks_enqueue_dynamic_css($data) {
//     $shortcode = 'uudis-kaks';
//     $atts = shortcode_parse_atts( $data['atts_string'] );
//     $atts = fw_ext_shortcodes_decode_attr($atts, $shortcode, $data['post']->ID);
//
//     wp_add_inline_style(
//         'theme-shortcode-'. $shortcode,
//         '#shortcode-'. $atts['id'] .' { '.
//             'color: '. $atts['color'] .';'.
//         ' } '
//     );
// }
//
// add_action(
//   'fw_ext_shortcodes_enqueue_static:uudis_kaks',
//   '_action_theme_shortcode_uudis_kaks_enqueue_dynamic_css'
// );
//
// endif;
