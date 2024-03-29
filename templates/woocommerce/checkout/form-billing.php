<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-billing-fields">
	<?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

		<h3><?php esc_html_e( 'Billing &amp; Shipping', 'woocommerce' ); ?></h3>

	<?php else : ?>

		<h3><?php esc_html_e( 'Billing details', 'woocommerce' ); ?></h3>

	<?php endif; ?>

	<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

	<div class="woocommerce-billing-fields__field-wrapper">
		<?php
		$fields = $checkout->get_checkout_fields( 'billing' );
		$first_part = ['billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone'];

		foreach ( $first_part as $field_name ) {
			if ( !isset( $fields[$field_name] ) ) continue;
			woocommerce_form_field( $field_name, $fields[$field_name], $checkout->get_value( $field_name ) );
		}
		?>
		<input type="hidden" name="is_checkout" value="true">
	</div>

	<?php if ( get_option('sp_pickup_delivery') ): ?>
	<p class="form-row form-row-wide js-layout-buttons">
		<?php $layout = get_option('sp_israel_delivery') ? 'israel_delivery' : 'international_delivery'; ?>
		<button data-layout="delivery" class="is-active"><?= __('Delivery', 'woocommerce') ?></button>
		<button data-layout="local_pickup">Local pickup</button>
	</p>
	<?php endif; ?>

	<?php
	if ( get_option('another_person_delivery') ) {
		require_once PLUGIN_DIR_PATH . 'template-parts/woocommerce/another_person_delivery.php';
	}
	?>

	<div class="woocommerce-billing-fields__field-wrapper js-delivery-fields-container">
		<?php
		if ( get_option('sp_israel_delivery') ) {
			require_once PLUGIN_DIR_PATH . 'template-parts/woocommerce/israel_delivery.php';
		} else {
			require_once PLUGIN_DIR_PATH . 'template-parts/woocommerce/international_delivery.php';
		}
		?>
	</div>

	<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="woocommerce-account-fields">
		<?php if ( ! $checkout->is_registration_required() ) : ?>

			<p class="form-row form-row-wide create-account">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ); ?> type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'woocommerce' ); ?></span>
				</label>
			</p>

		<?php endif; ?>

		<?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

		<?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

			<div class="create-account">
				<?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
					<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
	</div>
<?php endif; ?>
