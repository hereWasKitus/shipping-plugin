<?php
// TODO: make this function main for layout changes
add_action('wp_ajax_sp_layout_change', 'sp_layout_change');
add_action('wp_ajax_nopriv_sp_layout_change', 'sp_layout_change');

function sp_layout_change () {
  $layout = '';

  ob_start();

  require_once PLUGIN_DIR_PATH . "template-parts/woocommerce/{$_POST['template']}.php";

  $layout = ob_get_clean();

  wp_send_json([
    'success' => true,
    'data' => $layout
  ], 200);

  wp_die();
}

/**
 * JSON Generator
 */
add_action('wp_ajax_generate_json', 'generate_json');
function generate_json () {
  $order_id = $_POST['order_id'];
  $order = wc_get_order( $order_id );

  if ( !$order ) {
    wp_send_json([
      "success" => false,
      "error" => 'No such order'
    ]);
    wp_die();
  }

  $request_body = SP\Woocommerce\Woocommerce_Settings::create_api_request_body( $order_id );

  $json = str_replace('Array', '', json_encode($request_body, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

  $upload = wp_upload_bits( "order_$order_id.txt", null, $json );

  wp_send_json([
    "success" => $upload['error'] ? false : true,
    "error" => $upload['error'],
    "file" => $upload['error'] ? "" : $upload['url']
  ]);
}