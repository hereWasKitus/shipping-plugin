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

    <form class="js-options-form form-pickup-from-store" action="options.php" method="POST">
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
      'sp_pickup_delivery' => 'Enable pickup from store:',
      'sp_pickup_delivery_time' => 'Available pickup hours:',
      'sp_pickup_public_holidays' => 'Holidays:',
      'sp_pickup_show_person_blessing' => 'Show blessing on local pickup:'
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
    $schedule_array = json_decode($val, true);

    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    ?>

    <div class="sp-schedule">
      <?php foreach ($days as $day) : ?>
        <?php
        $preparation_time = isset($schedule_array[$day]['preparationTime']) ? esc_attr($schedule_array[$day]['preparationTime']) : '';
        ?>
        <div class="sp-schedule-day" data-day="<?= $day ?>">
          <h4 class="sp-schedule-day__title"><?= $day ?></h4>
          <ul class="sp-schedule-day__slots">
            <?php if ( count($schedule_array) && count( $schedule_array[$day]['slots'] ) ): ?>
              <?php foreach ( $schedule_array[$day]['slots'] as $time_slot ): ?>
                <li class="sp-schedule-day__slot">
                  <input placeholder="From" autocomplete="new-password" class="js-timepicker js-timepicker-60" type="text" required value="<?= $time_slot[0] ?>">
                  <input placeholder="To" autocomplete="new-password" class="js-timepicker js-timepicker-60" type="text" required value="<?= $time_slot[1] ?>">
                  <a href="#" class="js-remove-slot">
                    <i class="gg-trash"></i>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
          <button class="button button-primary js-add-schedule" data-interval="60">Add +</button>
          <input value="" class="next-day-delivery" type="hidden">
          <input autocomplete="new-password" value="<?= $preparation_time ?>" class="preparation-time" type="number" style="display: block; margin: 10px auto 0;" placeholder="Preparation time">
        </div>
      <?php endforeach; ?>
      <input class="sp-schedule-input" type="hidden" name="sp_pickup_delivery_time" value="<?php echo esc_attr($val) ?>" />
    </div>

    <?php
  }

  function sp_pickup_public_holidays_html () {
    $val = get_option('sp_pickup_public_holidays');
    ?>
    <div class="sp-public-holidays-container">
      <div id="sp-multi-datepicker"></div>
      <div id="sp-public-holidays"></div>
      <input class="sp-dates-input" type="hidden" name="sp_pickup_public_holidays" value="<?php echo esc_attr($val) ?>">
    </div>
    <?php
  }

  function sp_pickup_show_person_blessing_html () {
    $val = get_option('sp_pickup_show_person_blessing');
    ?>
    <input type="checkbox" name="sp_pickup_show_person_blessing" <?= esc_attr($val) ? 'checked' : '' ?>>
    <?php
  }
}
