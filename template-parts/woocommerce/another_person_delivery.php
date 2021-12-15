<?php
$checkout = WC() -> checkout;
$fields = $checkout->get_checkout_fields( 'billing' );

$included_fields = [
  'billing_another_person_delivery_first_name',
  'billing_another_person_delivery_last_name',
  'billing_another_person_delivery_phone_1',
  'billing_another_person_delivery_phone_2',
  'billing_another_person_work_place',
  'billing_another_person_blessing'
];

$blessings = json_decode(get_option('another_person_blessing'), true);
$checked = get_option('another_person_delivery_enabled');
?>
<div class="sp-another-person-delivery">
  <input type="checkbox" name="deliver_to_another_person" <?= esc_attr($checked) ? 'checked' : '' ?>><span>Deliver to another person?</span>
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

    <?php if ( count($blessings) ): ?>
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
    <?php endif; ?>
  </div>
</div>