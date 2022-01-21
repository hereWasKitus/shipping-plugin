<?php
  $checkout = WC() -> checkout;
  $fields = $checkout->get_checkout_fields( 'billing' );
  $blessings = json_decode(get_option('another_person_blessing'), true);

  $included_fields = [
    'billing_country',
    'billing_delivery_branch',
    'billing_delivery_day',
    'billing_delivery_timeset'
  ];

  foreach ( $included_fields as $field_name ) {
    if ( !isset( $fields[$field_name] ) ) continue;
    woocommerce_form_field(
      $field_name,
      $fields[$field_name],
      $field_name === 'billing_country' ? 'Israel' : $checkout->get_value( $field_name )
    );
  }

  echo "<input type=\"hidden\" name=\"delivery\" value=\"local_pickup\">";
?>