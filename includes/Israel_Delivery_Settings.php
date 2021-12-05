<?php
require_once __DIR__ . '/Setting_Page_Interface.php';
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

class Israel_Delivery_Settings implements Setting_Page_Interface {
  private $options_group;
  private $options_page_name;
  private $section_id;

  public function __construct() {
    $this->prefix = 'sp_israel';
    $this->options_group = 'israel_delivery';
    $this->options_page_name = 'israel_delivery';
    $this->section_id = 'israel_delivery';
    $this->setup_database();
  }

  public function page_html() {
  ?>
    <h1>Israel Delivery</h1>

    <form class="js-options-form" action="options.php" method="POST">
      <?php
      settings_fields( $this -> options_group );
      do_settings_sections( $this -> options_page_name );
      submit_button('Save changes', 'primary', '');
      ?>
    </form>
  <?php
  }

  public function setup_settings() {
    $settings = [
      'sp_israel_delivery' => 'Delivery within Israel:',
      'sp_israel_minimum_price_amount' => 'Minimum price amount:',
      'sp_israel_delivery_time' => 'Default business hours:',
      'sp_israel_public_holidays' => 'Public holidays:',
      'sp_israel_city_upload' => 'Upload CSV with cities:',
    ];

    add_settings_section($this -> section_id, '', '', $this -> options_page_name);

    foreach ($settings as $name => $title) {
      register_setting($this -> options_group, $name);
      add_settings_field($name, $title, [$this, "{$name}_html"], $this -> options_page_name, $this -> section_id);
    }
  }

  function setup_database () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $cities_table_name = $wpdb -> prefix . 'sp_delivery_cities';

    $cities_sql = "
    CREATE TABLE IF NOT EXISTS $cities_table_name (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name text NOT NULL,
      sku text NOT NULL,
      price float NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;
    ";

    dbDelta( $cities_sql );
  }

  function sp_israel_delivery_html () {
    $val = get_option('sp_israel_delivery');
    $val = $val ? $val : '';
    ?>
    <label class="switch">
      <input type="checkbox" name="sp_israel_delivery" <?= $val ? 'checked="cheked"' : '' ?>>
      <span class="slider round"></span>
    </label>
    <?php
  }

  function sp_israel_delivery_time_html () {
    $val = get_option('sp_israel_delivery_time');
    $schedule_array = json_decode($val, true);

    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    ?>

    <div class="sp-schedule">
      <?php foreach ($days as $day) : ?>
        <div class="sp-schedule-day" data-day="<?= $day ?>">
          <h4 class="sp-schedule-day__title"><?= $day ?></h4>
          <ul class="sp-schedule-day__slots">
            <?php if ( count($schedule_array) && count( $schedule_array[$day]['slots'] ) ): ?>
              <?php foreach ( $schedule_array[$day]['slots'] as $time_slot ): ?>
                <li class="sp-schedule-day__slot">
                  <input type="time" required value="<?= $time_slot[0] ?>">
                  <input type="time" required value="<?= $time_slot[1] ?>">
                  <a href="#" class="js-remove-slot">
                    <i class="gg-trash"></i>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <button class="button button-primary js-add-schedule">Add +</button>
          <input value="<?= esc_attr($schedule_array[$day]['nextDayDelivery']) ?>" class="next-day-delivery" type="time" style="display: block; margin: 10px auto 0;">
        </div>
      <?php endforeach; ?>
      <input class="sp-schedule-input" type="hidden" name="sp_israel_delivery_time" value="<?php echo esc_attr($val) ?>" />
    </div>

    <?php
  }

  function sp_israel_public_holidays_html () {
    $val = get_option('sp_israel_public_holidays');
    ?>
    <div class="sp-public-holidays-container">
      <div id="sp-multi-datepicker"></div>
      <div id="sp-public-holidays"></div>
      <input class="sp-dates-input" type="hidden" name="sp_israel_public_holidays" value="<?php echo esc_attr($val) ?>">
    </div>
    <?php
  }

  function sp_israel_city_upload_html () {
    $val = get_option('sp_israel_city_upload');
    $locations = json_decode($val, true);
  ?>
    <input type="file" class="js-file-upload" class="js-file-upload">

    <div class="sp-countries-container">
      <div class="sp-countries-list">
        <h4>Cities</h4>
        <ul>
        <?php if ( !$locations ): ?>
          <li>
            No fields
          </li>
        <?php else: ?>
          <?php foreach ( $locations as $sku => $data ): ?>
          <li>
            <input name="sku" placeholder="SKU" type="text" value="<?= $sku ?>">
            <input name="name" placeholder="Name" type="text" value="<?= $data['name'] ?>">
            <input name="price" placeholder="Price" type="number" value="<?= $data['price'] ?>">
            <a href="#" class="js-remove-location"><i class="gg-trash"></i></a>
          </li>
          <?php endforeach; ?>
        <?php endif; ?>
        </ul>
        <button class="button button-primary js-add-location">Add</button>
        <input type="hidden" name="sp_israel_city_upload" class="sp-locations-input" value="<?php echo esc_attr($val) ?>">
      </div>
    </div>

  <?php
  }

  function sp_israel_minimum_price_amount_html () {
    $val = get_option('sp_israel_minimum_price_amount');
    $val = $val ? $val : '';
    ?>
    <input type="number" name="sp_israel_minimum_price_amount" value="<?= $val ?>" placeholder="Enter minimum amount for other countries">
    <?php
  }
}
