<?php
require_once __DIR__ . '/Setting_Page_Interface.php';

class International_Delivery_Settings implements Setting_Page_Interface {
  private $prefix;
  private $options_group;
  private $options_page_name;
  private $section_id;

  public function __construct() {
    $this->prefix = 'sp_international';
    $this->options_group = 'international_delivery';
    $this->options_page_name = 'international_delivery';
    $this->section_id = 'international_delivery';
  }

  public function page_html() {
  ?>
    <h1>International Delivery</h1>

    <form action="options.php" method="POST">
      <?php
      settings_fields( $this -> options_group );
      do_settings_sections( $this -> options_page_name );
      submit_button();
      ?>
    </form>
  <?php
  }

  public function setup_settings() {
    $settings = [
      'sp_international_delivery' => 'International delivery:',
      'sp_international_delivery_time' => 'Default business hours:',
      'sp_international_public_holidays' => 'Public holidays:',
      'sp_international_country_upload' => 'Upload CSV with countries:',
      'sp_international_minimum_price_amount' => 'Minimum price amount:',
    ];

    add_settings_section($this -> section_id, '', '', $this -> options_page_name);

    foreach ($settings as $name => $title) {
      register_setting($this -> options_group, $name);
      add_settings_field($name, $title, [$this, "{$name}_html"], $this -> options_page_name, $this -> section_id);
    }
  }

  function sp_international_delivery_time_html() {
    $val = get_option('sp_international_delivery_time');

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    ?>

    <div class="sp-schedule">
      <?php foreach ($days as $day) : ?>
        <div class="sp-schedule-day">
          <h4 class="sp-schedule-day__title"><?= $day ?></h4>
          <ul class="sp-schedule-day__slots">
            <li class="sp-schedule-day__slot">
              <input type="time" step="600">
              <input type="time" step="600">
            </li>
          </ul>
          <button class="button button-primary">Add +</button>
        </div>
      <?php endforeach; ?>
    </div>
    <!-- <input type="text" name="sp_international_delivery_time" value="<?php echo esc_attr($val) ?>" /> -->

    <?php
  }

  function sp_international_delivery_html() {
    $val = get_option('sp_international_delivery');
    $val = $val ? $val : '';
    ?>
    <label class="switch">
      <input type="checkbox" name="sp_international_delivery" <?= $val ? 'checked="cheked"' : '' ?>>
      <span class="slider round"></span>
    </label>
    <?php
  }

  function sp_international_public_holidays_html() {
    $val = get_option('sp_international_public_holidays');
    ?>
    <div class="sp-public-holidays-container">
      <div id="sp-multi-datepicker"></div>
      <div id="sp-public-holidays"></div>
    </div>
    <?php
  }

  function sp_international_country_upload_html() {
  ?>
    <input type="file" name="sp_international_country_upload">

    <div class="sp-countries-container">
      <div class="sp-countries-list">
        <h4>Countries</h4>
        <ul>
          <li>
            <input type="text" placeholder="Name">
            <input type="text" placeholder="SKU">
            <input type="number" placeholder="Delivery price">
          </li>
        </ul>
      </div>
    </div>

  <?php
  }

  function sp_international_minimum_price_amount_html() {
    $val = get_option('sp_international_minimum_price_amount');
    $val = $val ? $val : '';
    ?>
    <input type="number" name="sp_international_minimum_price_amount" value="<?= $val ?>" placeholder="Enter minimum amount for other countries">
    <?php
  }
}
