<?php
/*
Plugin Name: Fix Multiple ReCaptchas
Plugin URI: http://nextwebllc.com
Description: Fixes error when trying to insert multiple Google ReCaptchas on a single page. Only
works with Ninja Forms 2.9.x
Version: 1.0
Author: Mike Harrison
Author URI: http://nextwebllc.com/our-vision#owner
License: GPLv2
*/
if( !defined('NW_GRER_PREFIX') ){
	define('NW_PREFIX', 'nw_grer_');
}

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( !is_plugin_active( 'ninja-forms/ninja-forms.php' ) )
	return;

add_action('plugins_loaded', function(){
	remove_action( 'init', 'ninja_forms_register_field_recaptcha' );
	add_action( 'init', NW_PREFIX.'ninja_forms_register_field_recaptcha' );
	add_action( 'wp_enqueue_scripts', NW_PREFIX.'enqueue_scripts' );
});

function nw_grer_enqueue_scripts(){
	wp_register_script(
		'fix-multiple-recaptchas',
		plugin_dir_url( __FILE__ ) . '/js/fix-multiple-recaptchas.js',
		[ 'jquery' ],
		false,
		true
	);
}

function nw_grer_ninja_forms_register_field_recaptcha() {

	$settings = get_option( "ninja_forms_settings" );
	$args     = array(
		'name'              => __( 'reCAPTCHA', 'ninja-forms' ),
		'sidebar'           => 'template_fields',
		'edit_function'     => '',
		'display_function'  => NW_PREFIX.'ninja_forms_field_recaptcha_display',
		'save_function'     => '',
		'group'             => 'standard_fields',
		'default_label'     => __( 'Confirm that you are not a bot', 'ninja-forms' ),
		'edit_label'        => true,
		'req'               => true,
		'edit_label_pos'    => true,
		'edit_req'          => false,
		'edit_custom_class' => false,
		'edit_help'         => false,
		'edit_meta'         => false,
		'edit_conditional'  => true,
		'conditional'       => array(
			'action' => array(
				'show' => array(
					'name'        => __( 'Show This', 'ninja-forms' ),
					'js_function' => 'show',
					'output'      => 'hide',
				),
				'hide' => array(
					'name'        => __( 'Hide This', 'ninja-forms' ),
					'js_function' => 'hide',
					'output'      => 'hide',
				),
			),
		),
		'display_label'     => true,
		'process_field'     => false,
		'pre_process'       => 'ninja_forms_field_recaptcha_pre_process',

	);
	// show recaptcha field in admin only if site and secret key exists.
	if ( ! empty( $settings['recaptcha_site_key'] ) && ! empty( $settings['recaptcha_secret_key']
		) ) {
		ninja_forms_register_field( '_recaptcha', $args );
	}

}

function nw_grer_ninja_forms_field_recaptcha_display( $field_id, $data, $form_id = '' ) {
	$settings = get_option( "ninja_forms_settings" );
	$lang = $settings['recaptcha_lang'];
	$siteKey = $settings['recaptcha_site_key'];
	$field_class = ninja_forms_get_field_class( $field_id, $form_id );
	$rand = wp_rand(0, 99999);
	wp_enqueue_script(
		'g-recaptcha',

		'https://www.google.com/recaptcha/api.js?onload=nf_grecaptcha_explicit_render&render=explicit&hl='.$lang,
		['fix-multiple-recaptchas'] );
	if ( !empty( $siteKey ) ) { ?>
		<input id="ninja_forms_field_<?php echo $rand;?>" name="ninja_forms_field_<?php echo
		$field_id;?>" type="hidden" class="<?php echo $field_class;?>" value="" rel="<?php echo $field_id;?>"
		/>
		<div class="g-recaptcha" data-callback="nf_recaptcha_set_field_value"
		     data-sitekey="<?php echo $siteKey; ?>"></div>
		<script type="text/javascript">
			function nf_recaptcha_set_field_value(inpval){
				jQuery("#ninja_forms_field_<?php echo $rand;?>").val(inpval);
			}
		</script>
		<?php
	}
}
