<?php if (!defined('FW')) die('Forbidden');

$uudis_shortcode = fw_ext('shortcodes')->get_shortcode('uudis_kaks');

$options = array(
	'id' => array( 'type' => 'unique' ),
	'link'   => array(
		'label' => __( 'RSS feed', 'fw' ),
		'desc'  => __( 'URL to RSS feed ', 'fw' ),
		'type'  => 'text',
		'value' => '#'
		),

	'news_category' => array(
	'type'  => 'select',
	'label' => __('News category', 'fw'),
	'desc'  => __('Select news category', 'fw'),
	'choices' => $uudis_shortcode->_get_category_dropdown_choices()
	),

	'mercury_key'   => array(
		'label' => __( 'Mecury API key', 'fw' ),
		'desc'  =>sprintf(
			__( 'Create a %sMERCURY%s web parser api key and add the Key here.', 'fw' ),
			'<a href="https://mercury.postlight.com/web-parser/">',
			'</a>'
		),
		'type'  => 'text',
		'value' => '#'
	),
	'show_preview' => array(
		'type'  => 'switch',
		'value' => false,
		'label' => __('Show article preview', 'fw'),
		'desc'  => __('Show short preview of the article.', 'fw'),
		'left-choice' => array(
			'value' => true,
			'label' => __('Yes', 'fw'),
		),
		'right-choice' => array(
			'value' => false,
			'label' => __('No', 'fw'),
		),
	),
	'news_height' => array(
	'type'  => 'select',
	'label' => __('News height', 'fw'),
	'desc'  => __('Select news height (full height 400 px)', 'fw'),
	'choices' => array(
		'full' => __('Full', 'fw'),
		'half' => __('1/2 ', 'fw'),
		),
	),

	'view' => array(
		'label'   => __('View', 'unyson'),
		'desc'    => __('Choose what view file should the shortcode pick', 'unyson'),
		'type'    => 'select',
		'choices' => array(
			'a'    => __('View A', 'unyson'),
			'b'    => __('View B', 'unyson'),
			'c'    => __('View C', 'unyson'),
			'rand' => __('Random View', 'unyson')
		)
	)
);
