<?php
require_once __DIR__ . '/Setting_Page_Interface.php';

class Pickup_From_Store_Settings implements Setting_Page_Interface {
  private $prefix;
  private $options_group;
  private $options_page_name;
  private $section_id;

  public function __construct() {
    $this->prefix = 'sp_pickup';
    $this->options_group = 'pickup_from_store';
    $this->options_page_name = 'pickup_from_store';
    $this->section_id = 'pickup_from_store';
  }

  public function page_html() {
  ?>
    <h1>Pickup from store</h1>

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
      'sp_pickup_delivery' => 'Enable pickup from store:',
      'sp_pickup_delivery_time' => 'Available pickup hours:',
      'sp_pickup_public_holidays' => 'Holidays:'
    ];

    add_settings_section($this -> section_id, '', '', $this -> options_page_name);

    foreach ($settings as $name => $title) {
      register_setting($this -> options_group, $name);
      add_settings_field($name, $title, [$this, "{$name}_html"], $this -> options_page_name, $this -> section_id);
    }
  }

  function sp_pickup_delivery_html () {
    $val = get_option('sp_pickup_delivery');
    $val = $val ? $val : '';
    ?>
    <label class="switch">
      <input type="checkbox" name="sp_pickup_delivery" <?= $val ? 'checked="cheked"' : '' ?>>
      <span class="slider round"></span>
    </label>
    <?php
  }

  function sp_pickup_delivery_time_html () {
    $val = get_option('sp_pickup_delivery_time');

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    ?>

    <div class="sp-schedule">
      <?php foreach ( $days as $day ): ?>
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
    <!-- <input type="text" name="sp_pickup_delivery_time" value="<?php echo esc_attr( $val ) ?>" /> -->

    <?php
  }

  function sp_pickup_public_holidays_html () {
    $val = get_option('sp_pickup_public_holidays');
    ?>
    <div class="sp-public-holidays-container">
      <div id="sp-multi-datepicker"></div>
      <div id="sp-public-holidays"></div>
    </div>
    <?php
  }
}
