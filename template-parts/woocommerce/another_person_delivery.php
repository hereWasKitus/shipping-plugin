<?php
$checkout = WC() -> checkout;
$fields = $checkout->get_checkout_fields( 'billing' );

$included_fields = [
  'billing_another_person_delivery_first_name',
  'billing_another_person_delivery_last_name',
  'billing_another_person_delivery_phone_1',
  'billing_another_person_delivery_phone_2',
  'billing_another_person_work_place'
];
?>
<div class="sp-another-person-delivery">
  <input type="checkbox" name="deliver_to_another_person"><span>Deliver to another person?</span>
  <div>
    <?php
    foreach ( $included_fields as $field_name ) {
      if ( !isset( $fields[$field_name] ) ) continue;
      woocommerce_form_field(
        $field_name,
        $fields[$field_name],
        ''
      );
    }
    ?>
  </div>
</div>