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

  $base_data = $order -> get_base_data();
  $user = get_user_by('email', $base_data['billing']['email']);
  $delivery_date = new DateTime( str_replace('/', '-', get_post_meta( $order_id, '_billing_delivery_day', true )) );
  $order_items = $order -> get_items();
	$delivery_timeset = get_post_meta( $order_id, '_billing_delivery_timeset', true );
	$is_local_pickup = !get_post_meta( $order->get_id(), '_billing_delivery_city', true ) && $base_data['billing']['country'] === 'Israel';

	$delivery_time = [
		"from" => $is_local_pickup ? $base_data['date_created'] -> date('G:i') : trim( explode('-', $delivery_timeset)[0] ),
		"to" => $is_local_pickup ? $delivery_timeset : trim( explode('-', $delivery_timeset)[1] )
	];

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
        'collection' => $is_local_pickup ? 0 : 1,
        'shipmentdate' => $delivery_date -> format('d-m-Y'),
        'fromtime' => $delivery_time['from'],
        'totime' => $delivery_time['to'],
        'company' => '',
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

  $json = str_replace('Array', '', json_encode($request_body));

  $upload = wp_upload_bits( "order_$order_id.txt", null, $json );

  wp_send_json([
    "success" => $upload['error'] ? false : true,
    "error" => $upload['error'],
    "file" => $upload['error'] ? "" : $upload['url']
  ]);
}