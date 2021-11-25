<?php
/**
 * TODO:
 * [-] add method to receive international delivery fields
 * [-] add method to receive israel delivery fields
 */
class Woocommerce_Settings {
  public function __construct() {
    $this -> setup_hooks();
  }

  public function setup_hooks () {
    add_action('wp_enqueue_scripts', [$this, 'wc_scripts']);
    add_filter( 'woocommerce_checkout_fields', [$this, 'checkout_fields'] );
    add_filter( 'woocommerce_checkout_fields', [$this, 'sp_checkout_fields'] );
    add_filter( 'woocommerce_checkout_fields', [$this, 'sp_another_person_fields'] );
    add_filter( 'woocommerce_locate_template', [$this, 'woo_adon_plugin_template'], 1, 3 );
    add_action( 'woocommerce_cart_calculate_fees', [$this, 'sp_add_cart_fee'] );
    add_action( 'woocommerce_admin_order_data_after_billing_address', [$this, 'sp_display_fields_in_order'] );
    add_filter( 'woocommerce_countries',  [$this, 'sp_woo_countries'] );
    add_action('woocommerce_after_checkout_billing_form', [$this, 'sp_deliver_to_another_pesron']);
    add_filter( 'woocommerce_after_checkout_validation', [$this, 'sp_checkout_validation'], 10, 2 );
  }

  public function wc_scripts () {
    wp_enqueue_script('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.js', ['jquery'], null, true);
    wp_enqueue_script( 'sp-checkout', PLUGIN_DIR . '/woocommerce/js/main.js', ['jquery-ui'], false, true );
    wp_localize_script('sp-checkout', 'wp', [
      'ajaxUrl' => admin_url('admin-ajax.php')
    ]);

    wp_enqueue_style('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.css');
    wp_enqueue_style('sp-main', PLUGIN_DIR . 'woocommerce/dist/main.css');
  }

  public function checkout_fields ( $fields ) {
    $new_fields = $fields;

    $new_fields['billing']['billing_email']['class'] = ['form-row-first'];

    $new_fields['billing']['billing_phone']['class'] = ['form-row-last'];

    $new_fields['billing']['billing_state']['required'] = false;

    return $new_fields;
  }

  public function sp_checkout_fields ( $fields ) {
    $domain = 'sp_woocommerce';

    // billing_delivery_countries
    // $fields['billing']['billing_delivery_countries'] = [
    //   'label'     => __('Country 1', $domain),
    //   'required'  => true,
    //   'clear'     => true,
    //   'class'     => ['select2', 'sp-wc-country'],
    //   'type'      => 'select',
    //   'options'   => $this -> get_countries_array()
    // ];

    $fields['billing']['billing_delivery_day'] = [
      'label'     => __('Day', $domain),
      'required'  => true,
      'class'     => ['sp-wc-calendar'],
      'clear'     => true
    ];

    $fields['billing']['billing_delivery_timeset'] = [
      'label'     => __('Time', $domain),
      'required'  => true,
      'class'     => ['sp-wc-time'],
      'clear'     => true,
      'type'      => 'select',
      'options'   => [
        '' => 'Select time'
      ]
    ];


    $fields['billing']['billing_delivery_city'] = [
      'label'     => __('Town / City 1', $domain),
      'required'  => true,
      'clear'     => true,
      'class'     => ['sp-wc-city'],
      'type'      => 'select',
      'options'   => $this -> get_cities_array()
    ];

    $fields['billing']['billing_delivery_house'] = [
      'label'     => __('House number', $domain),
      'required'  => false,
      'clear'     => true,
    ];

    $fields['billing']['billing_delivery_floor'] = [
      'label'     => __('Floor', $domain),
      'required'  => false,
      'clear'     => true,
    ];

    if ( isset($_POST['billing_country']) && $_POST['billing_country'] !== 'Israel' ) {
      $fields['billing']['billing_delivery_city']['required'] = false;
    }

    if ( isset($_POST['billing_country']) && $_POST['billing_country'] == 'Israel' ) {
      $fields['billing']['billing_city']['required'] = false;
      $fields['billing']['billing_postcode']['required'] = false;
    }

    if ( isset($_POST['delivery']) && $_POST['delivery'] == 'local_pickup' ) {
      $fields['billing']['billing_postcode']['required'] = false;
      $fields['billing']['billing_address_1']['required'] = false;
      $fields['billing']['billing_city']['required'] = false;
      $fields['billing']['billing_delivery_city']['required'] = false;
      $fields['billing']['billing_postcode']['required'] = false;
      $fields['billing']['billing_country']['required'] = false;
    }

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

  public function get_countries_array () {
    $countries = json_decode( get_option('sp_international_country_upload'), true);
    $countries_options = [
      '' => 'Select country',
      'Israel' => 'Israel'
    ];

    foreach ($countries as $sku => $country) {
      $countries_options[ $country['name'] ] = "{$country['name']} +{$country['price']}";
    }

    return $countries_options;
  }

  public function get_cities_array () {
    $cities = json_decode( get_option('sp_israel_city_upload'), true);
    $city_options = [
      '' => 'Select city'
    ];

    foreach ($cities as $city) {
      $city_options[ $city['name'] ] = "{$city['name']} +{$city['price']}";
    }

    return $city_options;
  }

  public function sp_add_cart_fee () {
    if ( ! $_POST || ( is_admin() && ! is_ajax() ) ) {
      return;
    }

    if ( isset( $_POST['post_data'] ) ) {
      parse_str( $_POST['post_data'], $post_data );
    } else {
      $post_data = $_POST;
    }

    if ( isset($post_data['billing_delivery_city']) && $post_data['billing_delivery_city'] && !isset($post_data['delivery']) ) {
      $needle = $post_data['billing_delivery_city'];
      $cities = json_decode(get_option('sp_israel_city_upload'), true);
      $city = array_values(array_filter( array_values($cities), function ($item) use ($needle) {
        return $item['name'] === $needle;
      } ));

      if ( $city && $city[0]['price'] ) {
        WC()->cart->add_fee( __('Shipping to city', 'woocommerce'), $city[0]['price'] );
      }

      return;
    }

    if ( isset($post_data['billing_country']) && $post_data['billing_country'] && !isset($post_data['delivery']) ) {
      $needle = $post_data['billing_country'];
      $countries = json_decode(get_option('sp_international_country_upload'), true);
      $country = array_values(array_filter( array_values($countries), function ($item) use ($needle) {
        return $item['name'] === $needle;
      } ));

      if ( $country && $country[0]['price'] ) {
        WC()->cart->add_fee( __('Shipping to country', 'woocommerce'), $country[0]['price'] );
      }

      return;
    }
  }

  public function sp_woo_countries( $countries ) {
    $international_delivery_countries = json_decode(get_option('sp_international_country_upload'), true);
    $new_countries = [];

    if ( get_option('sp_israel_delivery') ) {
      $new_countries = [
        'Israel' => 'Israel'
      ];
    }

    if ( get_option('sp_international_delivery') ) {
      foreach ($international_delivery_countries as $country) {
        $new_countries[ $country['name'] ] = "{$country['name']}";
      }
    }

    return $new_countries;
  }

  public function sp_deliver_to_another_pesron () {
    if ( get_option('another_person_delivery') ) {
      require_once PLUGIN_DIR_PATH . 'template-parts/woocommerce/another_person_delivery.php';
    }
  }

  public function sp_another_person_fields ( $fields ) {
    $first_name = json_decode(get_option('another_person_delivery_first_name'), true);
    $last_name = json_decode(get_option('another_person_delivery_last_name'), true);
    $phone_1 = json_decode(get_option('another_person_delivery_phone_1'), true);
    $phone_2 = json_decode(get_option('another_person_delivery_phone_2'), true);
    $work_place = json_decode(get_option('another_person_work_place'), true);

    $fields['billing']['billing_another_person_delivery_first_name'] = [
      'label' => $first_name['label'],
      'placeholder' => $first_name['placeholder'],
      'required' => $first_name['required']
    ];

    $fields['billing']['billing_another_person_delivery_last_name'] = [
      'label' => $last_name['label'],
      'placeholder' => $last_name['placeholder'],
      'required' => $last_name['required']
    ];

    $fields['billing']['billing_another_person_delivery_phone_1'] = [
      'label' => $phone_1['label'],
      'placeholder' => $phone_1['placeholder'],
      'required' => $phone_1['required']
    ];

    $fields['billing']['billing_another_person_delivery_phone_2'] = [
      'label' => $phone_2['label'],
      'placeholder' => $phone_2['placeholder'],
      'required' => $phone_2['required']
    ];

    $fields['billing']['billing_another_person_work_place'] = [
      'label' => $work_place['label'],
      'placeholder' => $work_place['placeholder'],
      'required' => $work_place['required']
    ];

    // If delivery to another person unselected
    if (  count( $_POST ) > 0 && !isset( $_POST['deliver_to_another_person'] ) ) {
      $fields['billing']['billing_another_person_delivery_first_name']['required'] = false;
      $fields['billing']['billing_another_person_delivery_last_name']['required'] = false;
      $fields['billing']['billing_another_person_delivery_phone_1']['required'] = false;
      $fields['billing']['billing_another_person_delivery_phone_2']['required'] = false;
      $fields['billing']['billing_another_person_work_place']['required'] = false;
    }

    return $fields;
  }

  public function sp_display_fields_in_order ($order) {
    echo '<p><strong>'.__('Country: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_country', true ) . '</p>';

    if ( !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && get_post_meta( $order->get_id(), '_billing_country', true ) === 'Israel' ) {
      echo '<p><strong>'.__('Delivery method: ').'</strong> ' . 'pickup from store' . '</p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_country', true ) === 'Israel' && get_post_meta( $order->get_id(), '_billing_delivery_city', true ) ) {
      echo '<p><strong>'.__('City: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_city', true ) . '</p>';
      echo '<p><strong>'.__('House: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_house', true ) . '</p>';
      echo '<p><strong>'.__('Floor: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_floor', true ) . '</p>';
    }

    echo '<p><strong>'.__('Delivery day: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_day', true ) . '</p>';
    echo '<p><strong>'.__('Delivery time: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ) . '</p>';

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) ) {
      echo '<h3>Delivery to another person</h3>';
      echo '<p><strong>'.__('First name: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) . '</p>';
      echo '<p><strong>'.__('Last name: ').'</strong> ' . get_post_meta( $order->get_id(), 'billing_another_person_delivery_last_name', true ) . '</p>';
      echo '<p><strong>'.__('Phone 1: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_1', true ) . '</p>';
      echo '<p><strong>'.__('Phone 2: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_2', true ) . '</p>';
      echo '<p><strong>'.__('Work place: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_another_person_delivery_work_place', true ) . '</p>';
    }
  }

  public function sp_checkout_validation ($fields, $errors) {
    $total = WC()->cart->cart_contents_total;

    $minimum_price = $fields['billing_country'] === 'Israel'
      ? get_option('sp_israel_minimum_price_amount')
      : get_option('sp_international_minimum_price_amount');

    if ( $total < $minimum_price && !isset($_POST['delivery']) ) {
      $errors->add( 'validation', 'Minimum order amount is: ' . $minimum_price . '$' );
    }

  }
}
