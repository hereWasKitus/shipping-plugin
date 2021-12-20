<?php
  $checkout = WC() -> checkout;
  $fields = $checkout->get_checkout_fields( 'billing' );
  $blessings = json_decode(get_option('another_person_blessing'), true);
  $show_blessing = get_option('sp_pickup_show_person_blessing');

  $included_fields = [
    'billing_country',
    'billing_delivery_day',
    'billing_delivery_timeset'
  ];

  if ( $show_blessing ) {
    array_push($included_fields, 'billing_another_person_blessing');
  }

  foreach ( $included_fields as $field_name ) {
    if ( !isset( $fields[$field_name] ) ) continue;
    woocommerce_form_field(
      $field_name,
      $fields[$field_name],
      $field_name === 'billing_country' ? 'Israel' : $checkout->get_value( $field_name )
    );
  }

  if ( $show_blessing && count($blessings) ): ?>
    <div class="form-row js-pickup-blessing">
      <button class="button button-primary" id="js-choose-blessing">Choose blessing</button>
      <div class="blessing-popup" id="blessing-popup">
        <div class="blessing-block" id="blessing-popup">
          <select id="js-blessing-category">
            <option disabled selected>Choose category</option>
            <?php foreach ( $blessings as $blessing ): ?>
            <option value="<?= $blessing['categoryName'] ?>"><?= $blessing['categoryName'] ?></option>
            <?php endforeach; ?>
          </select>
          <select id="js-blessing-message">
            <option disabled selected>Choose message</option>
          </select>
          <button class="js-close">Close</button>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <?php
  echo "<input type=\"hidden\" name=\"delivery\" value=\"local_pickup\">";
?>