<?php
require_once __DIR__ . '/International_Delivery_Settings.php';
require_once __DIR__ . '/Israel_Delivery_Settings.php';
require_once __DIR__ . '/Pickup_From_Store_Settings.php';
require_once __DIR__ . '/Another_Person_Delivery_Settings.php';

class Shipping_Plugin {
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

  function __construct () {
    $this -> setup_actions();
    $this -> setup_ajax();
  }

  function setup_actions () {
    $international_delivery = new International_Delivery_Settings();
    $israel_delivery = new Israel_Delivery_Settings();
    $pickup = new Pickup_From_Store_Settings();
    $another_person_delivery = new Another_Person_Delivery_Settings();

    add_action('admin_menu', [$this, 'setup_admin_pages']);

    add_action('admin_init', [$international_delivery, 'setup_settings']);
    add_action('admin_init', [$israel_delivery, 'setup_settings']);
    add_action('admin_init', [$pickup, 'setup_settings']);
    add_action('admin_init', [$another_person_delivery, 'setup_settings']);

    add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
  }

  function setup_ajax () {
    add_action('wp_ajax_sp_get_csv_content', [$this, 'sp_get_csv_content']);
    add_action('wp_ajax_sp_get_locations', [$this, 'sp_get_locations']);
    add_action('wp_ajax_sp_update_locations', [$this, 'sp_update_locations']);
    add_action('wp_ajax_sp_delete_locations', [$this, 'sp_delete_locations']);
    add_action('wp_ajax_sp_insert_locations', [$this, 'sp_insert_locations']);

    add_action('wp_ajax_get_option', [$this, 'get_option']);
    add_action('wp_ajax_nopriv_get_option', [$this, 'get_option']);
  }

  function admin_scripts () {
    wp_enqueue_script('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.js', ['jquery'], null, false);
    wp_enqueue_script('jquery-ui-multi-datepicker', PLUGIN_DIR . 'libs/jquery-ui-multi-datepicker/jquery-ui.multidatespicker.js', ['jquery-ui'], null, false);
    wp_enqueue_script('jquery-timepicker', PLUGIN_DIR . 'libs/jquery-timepicker/jquery.timepicker.min.js', ['jquery'], null, false);
    wp_enqueue_script('main', PLUGIN_DIR . 'admin/js/main.js', [], null, false);
    wp_localize_script('main', 'wpdata', [
      'ajaxUrl' => admin_url('admin-ajax.php')
    ]);

    wp_enqueue_style('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.css');
    wp_enqueue_style('jquery-ui-multi-datepicker', PLUGIN_DIR . 'libs/jquery-ui-multi-datepicker/jquery-ui.multidatespicker.css');
    wp_enqueue_style('jquery-timepicker', PLUGIN_DIR . 'libs/jquery-timepicker/jquery.timepicker.min.css');
    wp_enqueue_style('main', PLUGIN_DIR . 'admin/dist/main.css');
  }

  function setup_admin_pages () {
    $international_delivery = new International_Delivery_Settings();
    $israel_delivery = new Israel_Delivery_Settings();
    $pickup = new Pickup_From_Store_Settings();
    $another_person_delivery = new Another_Person_Delivery_Settings();

    add_menu_page( 'Shipping options', 'Shipping options', 'administrator', PLUGIN_SLUG );

    add_submenu_page( PLUGIN_SLUG, 'International delivery', 'International delivery', 'administrator', PLUGIN_SLUG, [$international_delivery, 'page_html'] );
    add_submenu_page( PLUGIN_SLUG, 'Delivery within Israel', 'Delivery within Israel', 'administrator', 'israel_delivery', [$israel_delivery, 'page_html'] );
    add_submenu_page( PLUGIN_SLUG, 'Pickup from store', 'Pickup from store', 'administrator', 'store_pickup', [$pickup, 'page_html'] );
    add_submenu_page( PLUGIN_SLUG, 'Delivery for another person', 'Delivery for another person', 'administrator', 'for_another_person', [$another_person_delivery, 'page_html'] );
  }

  function sp_get_csv_content () {
    global $wpdb;
    $stream = fopen( $_FILES['file']['tmp_name'], 'r' );
    $res = [];
    $prefix = $wpdb -> prefix;

    if ($stream !== FALSE) {
      while (($data = fgetcsv($stream, 1000, ",")) !== FALSE) {
        array_push( $res, $data );
      }
      fclose($stream);
    }

    $wpdb -> query("TRUNCATE TABLE {$prefix}{$_POST['table']}");

    foreach ($res as $data) {
      $wpdb->insert(
        $prefix . $_POST['table'],
        array(
          'name' => $data[1],
          'sku' => $data[0],
          'price' => $data[2],
        )
      );
    }

    $locations = $wpdb -> get_results("SELECT * FROM {$prefix}{$_POST['table']}");

    echo wp_json_encode( $locations );
    wp_die();
  }

  function sp_get_locations () {
    global $wpdb;
    $table = $wpdb -> prefix . $_POST['table_name'];
    $locations = $wpdb -> get_results("SELECT * FROM $table");
    wp_send_json( $locations );
  }

  function sp_update_locations () {
    global $wpdb;
    $locations = $_POST['items'];

    foreach ( $locations as $location ) {
      $wpdb -> update(
        $wpdb -> prefix . $_POST['table'],
        [
          'sku' => $location['sku'],
          'name' => $location['name'],
          'price' => $location['price'],
        ],
        [
          'id' => $location['id']
        ]
      );
    }

    wp_send_json( ['success' => true] );
  }

  function sp_delete_locations () {
    global $wpdb;
    $ids = $_POST['items'];

    foreach ($ids as $id) {
      $wpdb -> delete($wpdb -> prefix . $_POST['table'], ['id' => $id]);
    }

    wp_send_json( ['success' => true] );
  }

  function sp_insert_locations () {
    global $wpdb;
    $locations = $_POST['items'];

    foreach ( $locations as $location ) {
      $wpdb -> insert(
        $wpdb -> prefix . $_POST['table'],
        [
          'sku' => $location['sku'],
          'name' => $location['name'],
          'price' => $location['price'],
        ]
      );
    }

    wp_send_json( ['success' => true] );
  }

  function get_option () {
    echo get_option( $_POST['name'] );
    wp_die();
  }

}