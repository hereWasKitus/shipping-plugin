<?php

use function PHPSTORM_META\type;

class Woocommerce_Settings {
  public function __construct() {
    $this -> setup_hooks();
  }

  public function setup_hooks () {
    add_action('wp_enqueue_scripts', [$this, 'wc_scripts']);
    add_filter( 'woocommerce_checkout_fields' , [$this, 'checkout_fields'] );
    add_filter( 'woocommerce_checkout_fields' , [$this, 'sp_checkout_fields'] );
    add_filter( 'woocommerce_locate_template', [$this, 'woo_adon_plugin_template'], 1, 3 );
  }

  public function wc_scripts () {
    wp_enqueue_script('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.js', ['jquery'], null, true);
    wp_enqueue_script( 'sp-checkout', PLUGIN_DIR . '/woocommerce/js/main.js', ['jquery-ui'], false, true );

    wp_enqueue_style('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.css');
  }

  public function checkout_fields ( $fields ) {
    $new_fields = $fields;

    $new_fields['billing']['billing_first_name']['priority'] = 1;

    $new_fields['billing']['billing_last_name']['priority'] = 2;

    $new_fields['billing']['billing_email']['priority'] = 3;
    $new_fields['billing']['billing_email']['class'] = ['form-row-first'];

    $new_fields['billing']['billing_phone']['priority'] = 4;
    $new_fields['billing']['billing_phone']['class'] = ['form-row-last'];

    return $new_fields;
  }

  public function sp_checkout_fields ( $fields ) {
    $domain = 'sp_woocommerce';

    $fields['billing']['billing_delivery_day'] = [
      'label'     => __('Day', $domain),
      'required'  => true,
      'class'     => ['sp-wc-calendar'],
      'clear'     => true
    ];

    return $fields;
  }

  public function woo_adon_plugin_template( $template, $template_name, $template_path ) {
    global $woocommerce;
    $_template = $template;
    if ( ! $template_path )
      $template_path = $woocommerce->template_url;

    $plugin_path  = untrailingslashit( PLUGIN_DIR_PATH )  . '/templates/woocommerce/';

    $template = locate_template(
      array(
        $template_path . $template_name,
        $template_name
      )
    );

   if( ! $template && file_exists( $plugin_path . $template_name ) )
    $template = $plugin_path . $template_name;

   if ( ! $template )
    $template = $_template;

   return $template;
  }
}
