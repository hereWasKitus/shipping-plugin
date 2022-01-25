<?php
namespace SP\Woocommerce;
use DateTime;

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
    // add_action( 'woocommerce_admin_order_data_after_shipping_address', [$this, 'sp_display_fields_in_order'] );
    add_filter( 'woocommerce_countries',  [$this, 'sp_woo_countries'] );
    add_filter( 'woocommerce_after_checkout_validation', [$this, 'sp_checkout_validation'], 10, 2 );
    add_filter('woocommerce_email_order_meta_fields', [$this, 'my_custom_order_meta_keys'], 10, 3);
    add_action( 'woocommerce_new_order', [$this, 'modify_order_meta'] );
    add_filter( 'woocommerce_order_get_formatted_shipping_address', [$this, 'order_customer_detail_modifier'], 10, 3 );
  }

  public function wc_scripts () {
    if (is_checkout()) {
      wp_enqueue_script('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.js', ['jquery'], null, true);
      // wp_enqueue_script( 'sp-checkout', PLUGIN_DIR . '/woocommerce/js/main.js', ['jquery-ui'], false, true );
      wp_enqueue_script( 'sp-checkout', PLUGIN_DIR . '/woocommerce/js/common.js', ['jquery-ui'], false, true );
      wp_localize_script('sp-checkout', 'sp_data', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'tooltipText' => get_option('sp_international_tooltip'),
        'weekDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
        'holidays' => [
          'israel' => get_option('sp_israel_public_holidays'),
          'international' => get_option('sp_international_public_holidays'),
          'pickup' => get_option('sp_pickup_public_holidays')
        ],
        'deliveryTime' => [
          'israel' => get_option('sp_israel_delivery_time'),
          'international' => get_option('sp_international_delivery_time'),
          'pickup_branches' => get_option('sp_pickup_branches'),
        ],
        'contactReceiver' => [
          'international' => get_option('sp_international_contact_receiver'),
          'israel' => get_option('sp_israel_contact_receiver')
        ],
        'sameDayDelivery' => get_option('sp_international_same_day_delivery')
      ]);

      wp_enqueue_style('jquery-ui', PLUGIN_DIR . 'libs/jquery-ui/jquery-ui.min.css');
      wp_enqueue_style('sp-main', PLUGIN_DIR . 'woocommerce/dist/css/main.css');
    }
  }

  public function checkout_fields ( $fields ) {
    $new_fields = $fields;

    $new_fields['billing']['billing_email']['class'] = ['form-row-first'];

    $new_fields['billing']['billing_phone']['class'] = ['form-row-last'];
    $new_fields['billing']['billing_phone']['type'] = 'number';

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
      'clear'     => true,
      'custom_attributes' => [
        'readonly' => true,
        'autocomplete' => 'new-password'
      ]
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
      'type'      => 'number'
    ];

    $fields['billing']['billing_delivery_apartment'] = [
      'label'     => __('Apartment number', $domain),
      'required'  => false,
      'clear'     => true,
      'type'      => 'number'
    ];

    $fields['billing']['billing_delivery_floor'] = [
      'label'     => __('Floor', $domain),
      'required'  => false,
      'clear'     => true,
      'type'      => 'number'
    ];

    $fields['billing']['billing_delivery_region'] = [
      'label'     => __('Region', $domain),
      'required'  => false,
      'clear'     => true,
    ];

    $fields['billing']['billing_delivery_branch'] = [
      'required'  => false,
      'clear'     => true,
      'class'     => ['sp-wc-branches'],
      'type'      => 'select',
      'options'   => $this -> get_branches(),
    ];

    if ( isset($_POST['billing_country']) && $_POST['billing_country'] !== 'Israel' ) {
      $fields['billing']['billing_delivery_city']['required'] = false;
    }

    if ( isset($_POST['billing_country']) && $_POST['billing_country'] == 'Israel' ) {
      $fields['billing']['billing_city']['required'] = false;
      $fields['billing']['billing_postcode']['required'] = false;
    }

    if ( isset($_POST['delivery']) && $_POST['delivery'] == 'local_pickup' ) {
      $fields['billing']['billing_address_1']['required'] = false;
      $fields['billing']['billing_city']['required'] = false;
      $fields['billing']['billing_delivery_city']['required'] = false;
      $fields['billing']['billing_postcode']['required'] = false;
      $fields['billing']['billing_country']['required'] = false;
    }

    return $fields;
  }

  function get_branches() {
    $branches = json_decode(get_option('sp_pickup_branches'), true);
    $res = ['Select branch'];

    if (is_array($branches) && count($branches)) {
      foreach ($branches as $index => $branch ) {
        if ( isset($branch['isDisabled']) && $branch['isDisabled'] ) continue;
        $key = "{$branch['name']}_$index";
        $res[$key] = $branch['name'];
      }
    }

    return $res;
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
    global $wpdb;
    $table = $wpdb -> prefix . 'sp_delivery_cities';
    $cities = $wpdb -> get_results("SELECT * FROM $table");

    $city_options = [
      '' => 'Select city'
    ];

    foreach ($cities as $city) {
      $city_options[ $city -> name ] = "{$city -> name} +{$city -> price}";
    }

    return $city_options;
  }

  public function sp_add_cart_fee () {
    global $wpdb;

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

      $table = $wpdb -> prefix . 'sp_delivery_cities';
      $cities = $wpdb -> get_results("SELECT * FROM $table");

      $city = array_values(array_filter($cities, function ($item) use ($needle) {
        return $item -> name === $needle;
      }));

      if ( $city && $city[0] -> price ) {
        WC()->cart->add_fee( __('Shipping to city', 'woocommerce'), $city[0] -> price );
      }

      return;
    }

    if ( isset($post_data['billing_country']) && $post_data['billing_country'] && !isset($post_data['delivery']) ) {
      $needle = $post_data['billing_country'];

      $table = $wpdb -> prefix . 'sp_delivery_countries';
      $countries = $wpdb -> get_results("SELECT * FROM $table");

      $country = array_values(array_filter( $countries, function ($item) use ($needle) {
        return $item -> name === $needle;
      } ));

      if ( $country && $country[0] -> price ) {
        WC()->cart->add_fee( __('Shipping to country', 'woocommerce'), $country[0] -> price );
      }

      return;
    }
  }

  public function sp_woo_countries( $countries ) {
    global $wpdb;
    $table = $wpdb -> prefix . 'sp_delivery_countries';
    $international_delivery_countries = $wpdb -> get_results("SELECT * FROM $table");
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
        $new_countries[ $country -> name ] = "{$country -> name}";
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
      'required' => $phone_1['required'],
      'type'      => 'number'
    ];

    $fields['billing']['billing_another_person_delivery_phone_2'] = [
      'label' => $phone_2['label'],
      'placeholder' => $phone_2['placeholder'],
      'required' => $phone_2['required'],
      'type'      => 'number'
    ];

    $fields['billing']['billing_another_person_work_place'] = [
      'label' => $work_place['label'],
      'placeholder' => $work_place['placeholder'],
      'required' => $work_place['required']
    ];

    $fields['billing']['billing_another_person_blessing'] = [
      'label' => 'Blessing',
      'placeholder' => 'Your blessing',
      'type' => 'textarea'
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
    $is_local_pickup = !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && get_post_meta( $order->get_id(), '_billing_country', true ) === 'Israel';
    $is_contact_receiver = preg_match('/[a-zA-Z]/', get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ));

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) ) {
      $first_name = get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true );
      $last_name = get_post_meta( $order->get_id(), '_billing_another_person_delivery_last_name', true );
      echo '<p><strong>'.__('Full name: ')."</strong> </br> <span>" . "$first_name $last_name" . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_address_1', true ) ) {
      $street = get_post_meta( $order->get_id(), '_billing_address_1', true );
      $house = get_post_meta( $order->get_id(), '_billing_delivery_house', true );
      $val = $house ? "$street, $house" : $street;
      echo '<p><strong>'.__('Street: ')."</strong> </br> <span>" . $val . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_floor', true ) ) {
      echo '<p><strong>'.__('Floor: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_floor', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_apartment', true ) ) {
      echo '<p><strong>'.__('Apartment number: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_apartment', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_city', true ) ) {
      echo '<p><strong>'.__('City: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_city', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_region', true ) ) {
      echo '<p><strong>'.__('Region: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_region', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_postcode', true ) ) {
      echo '<p><strong>'.__('ZIP: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_postcode', true ) . '</span></p>';
    }

    echo '<p><strong>'.__('Country: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_country', true ) . '</span></p>';

    if ( get_post_meta( $order->get_id(), '_billing_another_person_work_place', true ) ) {
      echo '<p><strong>'.__('Work place: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_work_place', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_1', true ) ) {
      echo '<p><strong>'.__('Phone 1: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_1', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_2', true ) ) {
      echo '<p><strong>'.__('Phone 2: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_2', true ) . '</span></p>';
    }

    // local pickup branch
    // waiting...

    $day_title = $is_local_pickup ? 'Local pick up day: ' : 'Delivery day: ';
    $time_title = $is_local_pickup ? 'Local pick time: ' : 'Delivery time: ';

    echo '<p><strong>'.__($day_title)."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_day', true ) . '</span></p>';

    if ( !$is_contact_receiver ) {
      echo '<p><strong>'.__($time_title)."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_blessing', true ) ) {
      echo '<p><strong>'.__('Greeting message: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_blessing', true ) . '</span></p>';
    }

    // if ( !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && get_post_meta( $order->get_id(), '_billing_country', true ) === 'Israel' ) {
    //   echo '<p><strong>'.__('Delivery method: ')."</strong> </br> <span>" . 'pickup from store' . '</span></p>';
    // }

    // if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) ) {
    //   echo '<p><strong>'.__('First name: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) . '</span></p>';
    //   echo '<p><strong>'.__('Last name: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_delivery_last_name', true ) . '</span></p>';
    // }
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

  function my_custom_order_meta_keys ($fields, $sent_to_admin, $order) {
    $is_local_pickup = !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && get_post_meta( $order->get_id(), '_billing_country', true ) === 'Israel';
    $is_contact_receiver = preg_match('/[a-zA-Z]/', get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ));

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) ) {
      $first_name = get_post_meta($order -> get_id(), '_billing_another_person_delivery_first_name', true);
      $last_name = get_post_meta($order -> get_id(), '_billing_another_person_delivery_last_name', true);

      $fields['_billing_full_name'] = [
        'label' => 'Full name',
        'value' => "$first_name $last_name"
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_address_1', true ) ) {
      $street = get_post_meta( $order->get_id(), '_billing_address_1', true );
      $house = get_post_meta( $order->get_id(), '_billing_delivery_house', true );
      $val = $house ? "$street, $house" : $street;

      $fields['_billing_address_1'] = [
        'label' => 'Delivery street',
        'value' => $val
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_house', true ) ) {
      $fields['_billing_delivery_house'] = [
        'label' => 'Delivery house',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_house', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_floor', true ) ) {
      $fields['_billing_delivery_floor'] = [
        'label' => 'Floor',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_floor', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_apartment', true ) ) {
      $fields['_billing_delivery_apartment'] = [
        'label' => 'Delivery apartment number',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_apartment', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_city', true ) ) {
      $fields['_billing_delivery_city'] = [
        'label' => 'Delivery city',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_city', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_delivery_region', true ) ) {
      $fields['_billing_delivery_region'] = [
        'label' => 'Delivery region',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_region', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_postcode', true ) ) {
      $fields['_billing_postcode'] = [
        'label' => 'ZIP',
        'value' => get_post_meta( $order->get_id(), '_billing_postcode', true )
      ];
    }

    if ( !$is_local_pickup && get_post_meta( $order->get_id(), '_billing_country', true ) ) {
      $fields['_billing_country'] = [
        'label' => 'Delivery country',
        'value' => get_post_meta( $order->get_id(), '_billing_country', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_work_place', true ) ) {
      echo '<p><strong>'.__('Workplace: ')."</strong> </br> <span>" . get_post_meta( $order->get_id(), '_billing_another_person_work_place', true ) . '</span></p>';
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_1', true ) ) {
      $fields['_billing_another_person_delivery_phone_1'] = [
        'label' => 'Recipient phone 1',
        'value' => get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_1', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_2', true ) ) {
      $fields['_billing_another_person_delivery_phone_2'] = [
        'label' => 'Recipient phone 2',
        'value' => get_post_meta( $order->get_id(), '_billing_another_person_delivery_phone_2', true )
      ];
    }

    // local delivery branch

    if ( get_post_meta( $order->get_id(), '_billing_delivery_day', true ) ) {
      $fields['_billing_delivery_day'] = [
        'label' => $is_local_pickup ? 'Local pick up day' : 'Delivery day',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_day', true )
      ];
    }

    if ( !$is_contact_receiver && get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ) ) {
      $fields['_billing_delivery_timeset'] = [
        'label' => $is_local_pickup ? 'Local pick up time' : 'Delivery time',
        'value' => get_post_meta( $order->get_id(), '_billing_delivery_timeset', true )
      ];
    }

    if ( get_post_meta( $order->get_id(), '_billing_another_person_blessing', true ) ) {
      $fields['_billing_another_person_blessing'] = [
        'label' => 'Greeting message',
        'value' => get_post_meta( $order->get_id(), '_billing_another_person_blessing', true )
      ];
    }

    // if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true ) ) {
    //   $fields['_billing_another_person_delivery_first_name'] = [
    //     'label' => 'Recipient first name',
    //     'value' => get_post_meta( $order->get_id(), '_billing_another_person_delivery_first_name', true )
    //   ];
    // }

    // if ( get_post_meta( $order->get_id(), '_billing_another_person_delivery_last_name', true ) ) {
    //   $fields['_billing_another_person_delivery_last_name'] = [
    //     'label' => 'Recipient last name',
    //     'value' => get_post_meta( $order->get_id(), '_billing_another_person_delivery_last_name', true )
    //   ];
    // }

    return $fields;
  }

  function modify_order_meta ( $order_id ) {
    global $wpdb;
    $order = wc_get_order( $order_id );
    $base_data = $order -> get_base_data();
    $country = $base_data['billing']['country'];
    $city = $base_data['billing']['city'] ?: get_post_meta( $order_id, '_billing_delivery_city', true );
    $result = '';

    if ( strtolower($country) === 'israel' && !$city ) {
      update_post_meta($order_id, '_sku', '');
      return;
    }

    if ( strtolower($country) === 'israel' ) {
      $table = $wpdb -> prefix . 'sp_delivery_cities';
      $result = $wpdb -> get_results("SELECT `sku`, `price` FROM $table WHERE `name` = '$city' LIMIT 1");
    } else {
      $table = $wpdb -> prefix . 'sp_delivery_countries';
      $result = $wpdb -> get_results("SELECT `sku`, `price` FROM $table WHERE `name` = '$country' LIMIT 1");
    }

    $sku = count($result) ? $result[0] -> sku : '';
    $price = count($result) ? $result[0] -> price : 0;
    update_post_meta($order_id, '_sku', $sku);
    update_post_meta($order_id, '_shipping_cost', $price);
  }

  public static function create_api_request_body ( $order_id ) {
    $order = wc_get_order( $order_id );
    $base_data = $order -> get_base_data();
    $datetime = new DateTime();
    $is_international = $base_data['billing']['country'] && $base_data['billing']['country'] !== 'Israel';
    $delivery_date = $datetime -> createFromFormat('d/m/Y', get_post_meta( $order_id, '_billing_delivery_day', true ));
    $is_contact_receiver = preg_match('/[a-zA-Z]/', get_post_meta( $order_id, '_billing_delivery_timeset', true ));
    $delivery_timeset = get_post_meta( $order_id, '_billing_delivery_timeset', true );
    $is_local_pickup = !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && $base_data['billing']['country'] === 'Israel';
    $delivery_time = false;

    if ( !$is_contact_receiver ) {
      $delivery_time = [
        "from" => $is_local_pickup ? $base_data['date_created'] -> date('G:i') : trim( explode('-', $delivery_timeset)[0] ),
        "to" => $is_local_pickup ? $delivery_timeset : trim( explode('-', $delivery_timeset)[1] )
      ];
    }
    $paydate = $order -> get_date_paid() ?? $order -> get_date_created();

    $city_field_data = array_filter([
      get_post_meta( $order->get_id(), '_billing_delivery_region', true ),
      $base_data['billing']['city'] ?: get_post_meta( $order->get_id(), '_billing_delivery_city', true ),
      $is_international ? $base_data['billing']['country'] : ''
    ]);

    $request_body = [
      'Orderid' => $order_id,
      'OrderTitle' => $base_data['order_key'],
      'OrderStatus' => $base_data['status'],
      'Cust' => [
        'custId' => get_post_meta($order_id, '_customer_user', true),
        'custName' => "{$base_data['billing']['first_name']} {$base_data['billing']['last_name']}",
        'custCity' => join(', ', $city_field_data),
        'custAddress' => $base_data['billing']['address_1'],
        'custTel' => $base_data['billing']['phone'],
        'custEmail' => $base_data['billing']['email']
      ],
      'shipment' => [
        [
          'collection' => $is_local_pickup ? 0 : 1,
          'shipmentdate' => $delivery_date -> format('d-m-Y'),
          'company' => get_post_meta( $order_id, '_billing_another_person_work_place', true ),
          'firstname' => get_post_meta( $order_id, '_billing_another_person_delivery_first_name', true ),
          'lastname' => get_post_meta( $order_id, '_billing_another_person_delivery_last_name', true ),
          'tel1' => get_post_meta( $order_id, '_billing_another_person_delivery_phone_1', true ),
          'tel2' => get_post_meta( $order_id, '_billing_another_person_delivery_phone_2', true ),
          'street' => $base_data['billing']['address_1'],
          'number' => get_post_meta( $order->get_id(), '_billing_delivery_house', true ),
          'entrance' => get_post_meta( $order->get_id(), '_billing_delivery_apartment', true ),
          'floor' => get_post_meta( $order->get_id(), '_billing_delivery_floor', true ),
          'note' => $base_data['customer_note'],
          'blessing' => get_post_meta( $order->get_id(), '_billing_another_person_blessing', true ),
        ]
      ],
      'OrderItems' => [],
      'OrderPayment' => [
        'PayDate' => $paydate -> date('Y-m-d'),
        'NumberOfPayments' => 1,
        'FirstPayment' => $order -> get_total('edit')
      ]
    ];

    if ( $delivery_time ) {
      $request_body['shipment']['fromtime'] = $delivery_time['from'];
      $request_body['shipment']['totime'] = $delivery_time['to'];
    }

    if ( $base_data['payment_method'] === 'zcredit_checkout_payment' ) {
		$payment_response = get_post_meta( $order_id, 'zc_response', true );
		$payment_info = json_decode(unserialize(base64_decode($payment_response)), true);
		$payment_date = str_replace('/', '', $payment_info['ExpDate_MMYY']);

		$order_payment = [];

		if ( $payment_info['Installments'] > 1 ) {
			for ( $i = 0; $i < $payment_info['Installments']; $i ++ ) {
				$first_payment = $i === 0 ? $payment_info['FirstInstallentAmount'] : $payment_info['OtherInstallmentsAmount'];
				array_push(
					$order_payment,
					[
						'PayDate' => $paydate -> date('Y-m-d'),
						'CreditName' => 'אשראי',
						'CreditDateEnd' => $payment_date,
						'CreditNumber' => $payment_info['CardNum'],
						'NumberOfPayments' => $payment_info['Installments'],
						'FirstPayment' => $first_payment,
						'ReceiptNumber' => $payment_info['ApprovalNumber']
					]
				);
			}
		} else {
			array_push(
				$order_payment,
				[
					'PayDate' => $paydate -> date('Y-m-d'),
					'CreditName' => 'אשראי',
					'CreditDateEnd' => $payment_date,
					'CreditNumber' => $payment_info['CardNum'],
					'NumberOfPayments' => $payment_info['Installments'],
					'FirstPayment' => $payment_info['Total'],
					'ReceiptNumber' => $payment_info['ApprovalNumber']
				]
			);
		}

		$request_body['OrderPayment'] = $order_payment;
	}

    foreach ($order -> get_items() as $item) {
      $product = $item -> get_product();
      $cart_discount = get_post_meta($order_id, '_cart_discount', true) ?: 0;
      $points_discount = get_post_meta($order_id, '_ywpar_coupon_amount', true) ?: 0;
      $cart_discount_without_points = $cart_discount - $points_discount;
      $cart_percent_discount = '';

      if ( $cart_discount_without_points ) {
        $cart_percent_discount = ($cart_discount_without_points / $order -> get_subtotal()) * 100;
      }

      $request_body['OrderItems'][] = [
        'ItemId' => $product -> get_ID(),
        'ItemDesc' => $product -> get_description(),
        'ItemQty' => $item -> get_quantity(),
        'UnitPrice' => $product -> get_price(),
        'discount' => $cart_percent_discount
      ];
    }

    // Add award points
    if ( $points_amount = get_post_meta($order_id, '_ywpar_coupon_amount', true) ) {
      $points_count = get_post_meta($order_id, '_ywpar_coupon_points', true);
      // $percent = ($points_amount / $order -> get_subtotal()) * 100;
      $request_body['OrderItems'][] = [
        'ItemId' => 2822,
        'ItemDesc' => '',
        'ItemQty' => $points_count * -1,
        'UnitPrice' => '0.05',
        'discount' => ''
      ];
    }

    // Add shipping item
    if ( $sku = get_post_meta($order_id, '_sku', true) ) {
      $request_body['OrderItems'][] = [
        'ItemId' => $sku,
        'ItemDesc' => '',
        'ItemQty' => 1,
        'UnitPrice' => get_post_meta($order_id, '_shipping_cost', true),
        'discount' => ''
      ];
    }

    return $request_body;
  }

  function order_customer_detail_modifier ( $address, $raw_address, $order ) {
    $order_id = $order -> get_id();
    $html = "";
    $country = get_post_meta($order_id, '_billing_country', true);
    $is_local_pickup = !get_post_meta( $order_id, '_billing_delivery_city', true ) && $country === 'Israel';
    $is_contact_receiver = preg_match('/[a-zA-Z]/', get_post_meta( $order_id, '_billing_delivery_timeset', true ));

    if ( get_post_meta( $order_id, '_billing_another_person_delivery_first_name', true ) ) {
      $first_name = get_post_meta( $order_id, '_billing_another_person_delivery_first_name', true );
      $last_name = get_post_meta( $order_id, '_billing_another_person_delivery_last_name', true );
      $html .= "Full name: $first_name $last_name<br>";
    }

    if ( get_post_meta( $order_id, '_billing_address_1', true ) ) {
      $street = get_post_meta( $order_id, '_billing_address_1', true );
      $house = get_post_meta( $order_id, '_billing_delivery_house', true );
      $val = $house ? "$street, $house" : $street;
      $html .= "Street: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_delivery_floor', true ) ) {
      $val = get_post_meta( $order_id, '_billing_delivery_floor', true );
      $html .= "Floor: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_delivery_apartment', true ) ) {
      $val = get_post_meta( $order_id, '_billing_delivery_apartment', true );
      $html .= "Apartment: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_delivery_region', true ) ) {
      $val = get_post_meta( $order_id, '_billing_delivery_region', true );
      $html .= "Region: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_postcode', true ) ) {
      $val = get_post_meta( $order_id, '_billing_postcode', true );
      $html .= "ZIP: $val<br>";
    }

    if ( $country ) {
      $html .= "Country: $country<br>";
    }

    if ( get_post_meta( $order_id, '_billing_another_person_work_place', true ) ) {
      $val = get_post_meta( $order_id, '_billing_another_person_work_place', true );
      $html .= "Work place: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_another_person_delivery_phone_1', true ) ) {
      $val = get_post_meta( $order_id, '_billing_another_person_delivery_phone_1', true );
      $html .= "Phone 1: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_another_person_delivery_phone_2', true ) ) {
      $val = get_post_meta( $order_id, '_billing_another_person_delivery_phone_2', true );
      $html .= "Phone 2: $val<br>";
    }

    if ( get_post_meta( $order_id, '_billing_delivery_branch', true ) ) {
      $val = get_post_meta( $order_id, '_billing_delivery_branch', true );
      $val = substr($val, 0, strlen($val) - 2); // remove postfix
      $html .= "Local delivery branch: $val<br>";
    }

    // delivery branch

    $day_title = $is_local_pickup ? 'Local pick up day: ' : 'Delivery day: ';
    $time_title = $is_local_pickup ? 'Local pick time: ' : 'Delivery time: ';

    $html .= __($day_title) . " " . get_post_meta( $order->get_id(), '_billing_delivery_day', true ) . "<br>";

    if ( !$is_contact_receiver ) {
      $html .= __($time_title) . " " . get_post_meta( $order->get_id(), '_billing_delivery_timeset', true ) . "<br>";
    }

    if ( get_post_meta( $order_id, '_billing_another_person_blessing', true ) ) {
      $val = get_post_meta( $order_id, '_billing_another_person_blessing', true );
      $html .= "Greeting: $val<br>";
    }

    return $html;
  }
}
