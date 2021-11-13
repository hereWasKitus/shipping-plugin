<?php
require_once __DIR__ . '/Setting_Page_Interface.php';

class Another_Person_Delivery_Settings implements Setting_Page_Interface {
  private $prefix;
  private $options_group;
  private $options_page_name;
  private $section_id;

  public function __construct() {
    $this->prefix = 'sp_another_person';
    $this->options_group = 'another_person';
    $this->options_page_name = 'another_person';
    $this->section_id = 'another_person';
  }

  public function page_html() {
  ?>
    <h1>Delivery for another person</h1>

    <form action="options.php" method="POST">
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
      'another_person_delivery' => 'Enable delivery for another person:',
      'another_person_delivery_first_name' => 'First name field settings:',
      'another_person_delivery_last_name' => 'Last name field settings:',
      'another_person_delivery_phone_1' => 'Phone number #1 field settings:',
      'another_person_delivery_phone_2' => 'Phone number #1 field settings:',
      'another_person_work_place' => 'Work place field settings:',
      'another_person_order_note' => 'Order note field settings:',
    ];

    add_settings_section($this -> section_id, '', '', $this -> options_page_name);

    foreach ($settings as $name => $title) {
      register_setting($this -> options_group, $name);
      add_settings_field($name, $title, [$this, "{$name}_html"], $this -> options_page_name, $this -> section_id);
    }
  }

  function another_person_delivery_first_name_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_delivery_last_name_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_delivery_phone_1_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label">
      </li>
      <li>
       Placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_delivery_phone_2_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        First name label: <input type="text" placeholder="Label">
      </li>
      <li>
        First name placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_work_place_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        First name label: <input type="text" placeholder="Label">
      </li>
      <li>
        First name placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_order_note_html () {
    ?>
    <ul class="sp-field-settings">
      <li>
        First name label: <input type="text" placeholder="Label">
      </li>
      <li>
        First name placeholder: <input type="text" placeholder="Placeholder">
      </li>
      <li>
        Required: <input type="checkbox">
      </li>
      <li>
        Show this field: <input type="checkbox">
      </li>
    </ul>
    <?php
  }

  function another_person_delivery_html () {
    $val = get_option('another_person_delivery');
    $val = $val ? $val : '';
    ?>
    <label class="switch">
      <input type="checkbox" name="another_person_delivery" <?= $val ? 'checked="cheked"' : '' ?>>
      <span class="slider round"></span>
    </label>
    <?php
  }
}
