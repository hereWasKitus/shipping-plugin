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