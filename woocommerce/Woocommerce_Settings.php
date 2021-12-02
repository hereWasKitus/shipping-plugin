<?php
/**
 * TODO:
 * [-] add method to receive international delivery fields
 * [-] add method to receive israel delivery fields
 */
use GuzzleHttp\Client;

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
    add_filter( 'woocommerce_after_checkout_validation', [$this, 'sp_checkout_validation'], 10, 2 );
    // add_action( 'woocommerce_new_order', [$this, 'api_integration'], 10, 1 );
    add_action( 'woocommerce_thankyou', [$this, 'api_integration'], 10, 1 );
  }

  public function wc_scripts () {
    if (is_checkout()) {
      wp_enqueue_script('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.js', ['jquery'], null, true);
      wp_enqueue_script( 'sp-checkout', PLUGIN_DIR . '/woocommerce/js/main.js', ['jquery-ui'], false, true );
      wp_localize_script('sp-checkout', 'wp', [
        'ajaxUrl' => admin_url('admin-ajax.php')
      ]);

      wp_enqueue_style('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.css');
      wp_enqueue_style('sp-main', PLUGIN_DIR . 'woocommerce/dist/main.css');
    }
  }

  public function checkout_fields ( $fields ) {
    $new_fields = $fields;

    $new_fields['billing']['billing_email']['class'] = ['form-row-first'];

    $new_fields['billing']['billing_phone']['class'] = ['form-row-last'];

    $new_fields['billing']['billing_state']['required'] = false;

    $new_fields['billing']['billing_country']['label'] = 'Country';

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

    $fields['billing']['billing_delivery_region'] = [
      'label'     => __('Region', $domain),
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

    if (
      (isset($_POST['delivery']) && $_POST['delivery'] === 'local_pickup') ||
      (isset($_POST['template']) && $_POST['template'] === 'local_pickup')
    ) {
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

    $fields['billing']['billing_another_person_blessing'] = [
      'label' => 'Blessing',
      'placeholder' => 'Your blessing'
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
    echo '<p><strong>'.__('Region: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_delivery_region', true ) . '</p>';

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
      echo '<p><strong>'.__('Blessing message: ').'</strong> ' . get_post_meta( $order->get_id(), '_billing_another_person_blessing', true ) . '</p>';
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

  public function api_integration ( $order_id ) {
    $client = new Client();
    $order = wc_get_order( $order_id );
    $base_data = $order -> get_base_data();
    $user = get_user_by('email', $base_data['billing']['email']);
    $delivery_date = new DateTime( get_post_meta( $order_id, '_billing_delivery_day', true ) );
    $from_time = $base_data['date_created'] -> date('G:i');
    $order_items = $order -> get_items();

    $request_body = [
      'Orderid' => $order_id,
      'OrderTitle' => $base_data['order_key'],
      'OrderStatus' => $base_data['status'],
      'Cust' => [
        'custId' => $user ? $user -> ID : '',
        'custName' => "{$base_data['billing']['first_name']} {$base_data['billing']['last_name']}",
        'custCity' => $base_data['billing']['city'] ? $base_data['billing']['city'] : get_post_meta( $order->get_id(), '_billing_delivery_city', true ),
        'custAddress' => $base_data['billing']['address_1'],
        'custTel' => $base_data['billing']['phone'],
        'custEmail' => $base_data['billing']['email']
      ],
      'shipment' => [
        [
          'collection' => !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && $base_data['billing']['country'] === 'Israel' ? 0 : 1,
          'shipmentdate' => $delivery_date -> format('d-m-Y'),
          'fromtime' => $from_time,
          'totime' => get_post_meta( $order_id, '_billing_delivery_timeset', true ),
          'company' => '',
          'firstname' => get_post_meta( $order_id, '_billing_another_person_delivery_first_name', true ),
          'lastname' => get_post_meta( $order_id, '_billing_another_person_delivery_last_name', true ),
          'tel1' => get_post_meta( $order_id, '_billing_another_person_delivery_phone_1', true ),
          'tel2' => get_post_meta( $order_id, '_billing_another_person_delivery_phone_2', true ),
          'street' => $base_data['billing']['address_1'],
          'number' => get_post_meta( $order->get_id(), '_billing_delivery_house', true ),
          'entrance' => '',
          'floor' => get_post_meta( $order->get_id(), '_billing_delivery_floor', true ),
          'note' => $base_data['customer_note'],
          'blessing' => '',
        ]
      ],
      'OrderItems' => []
    ];

    foreach ($order_items as $item_id => $item) {
      $product = $item -> get_product();
      $request_body['OrderItems'][] = [
        'ItemId' => $product -> get_ID(),
        'ItemDesc' => $product -> get_description(),
        'ItemQty' => $item -> get_quantity(),
        'UnitPrice' => $product -> get_price(),
        'discount' => '',
      ];
    }

    $response = $client -> post('http://62.90.195.20/PostDimona.aspx?Api_key=8e60d3d7-d27c-490a-adc0-32fdfb51d3f0-Irit&Order=' . json_encode($request_body));
  }
}
