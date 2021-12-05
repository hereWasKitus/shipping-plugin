<?php
require_once __DIR__ . '/Setting_Page_Interface.php';

class Another_Person_Delivery_Settings implements Setting_Page_Interface {
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

    <form action="options.php" method="POST" class="js-options-form">
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
      'another_person_delivery_enabled' => 'Checkbox checked by default on checkout page:',
      'another_person_delivery_first_name' => 'First name field settings:',
      'another_person_delivery_last_name' => 'Last name field settings:',
      'another_person_delivery_phone_1' => 'Phone number #1 field settings:',
      'another_person_delivery_phone_2' => 'Phone number #2 field settings:',
      'another_person_work_place' => 'Work place field settings:',
      'another_person_blessing' => 'Blessings:'
    ];

    add_settings_section($this -> section_id, '', '', $this -> options_page_name);

    foreach ($settings as $name => $title) {
      register_setting($this -> options_group, $name);
      add_settings_field($name, $title, [$this, "{$name}_html"], $this -> options_page_name, $this -> section_id);
    }
  }

  function another_person_delivery_enabled_html () {
    $val = get_option('another_person_delivery_enabled');
    ?>
    <input type="checkbox" name="another_person_delivery_enabled" id="another_person_delivery_enabled" <?= esc_attr($val) ? 'checked' : '' ?>>
    <?php
  }

  function another_person_delivery_first_name_html () {
    $values = json_decode(get_option('another_person_delivery_first_name'), true);
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label" value="<?= $values['label'] ?>">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder" value="<?= $values['placeholder'] ?>">
      </li>
      <li>
        Required: <input type="checkbox"  <?= isset($values['required']) && $values['required'] ? "checked=\"checked\"" : '' ?>>
      </li>
      <input type="hidden" name="another_person_delivery_first_name" value="<?= get_option('another_person_delivery_first_name') ?>">
    </ul>
    <?php
  }

  function another_person_delivery_last_name_html () {
    $values = json_decode(get_option('another_person_delivery_last_name'), true);
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label" value="<?= $values['label'] ?>">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder" value="<?= $values['placeholder'] ?>">
      </li>
      <li>
        Required: <input type="checkbox"  <?= isset($values['required']) && $values['required'] ? "checked=\"checked\"" : '' ?>>
      </li>
      <input type="hidden" name="another_person_delivery_last_name" value="<?= get_option('another_person_delivery_last_name') ?>">
    </ul>
    <?php
  }

  function another_person_delivery_phone_1_html () {
    $values = json_decode(get_option('another_person_delivery_phone_1'), true);
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label" value="<?= $values['label'] ?>">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder" value="<?= $values['placeholder'] ?>">
      </li>
      <li>
        Required: <input type="checkbox"  <?= isset($values['required']) && $values['required'] ? "checked=\"checked\"" : '' ?>>
      </li>
      <input type="hidden" name="another_person_delivery_phone_1" value="<?= get_option('another_person_delivery_phone_1') ?>">
    </ul>
    <?php
  }

  function another_person_delivery_phone_2_html () {
    $values = json_decode(get_option('another_person_delivery_phone_2'), true);
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label" value="<?= $values['label'] ?>">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder" value="<?= $values['placeholder'] ?>">
      </li>
      <li>
        Required: <input type="checkbox"  <?= isset($values['required']) && $values['required'] ? "checked=\"checked\"" : '' ?>>
      </li>
      <input type="hidden" name="another_person_delivery_phone_2" value="<?= get_option('another_person_delivery_phone_2') ?>">
    </ul>
    <?php
  }

  function another_person_work_place_html () {
    $values = json_decode(get_option('another_person_work_place'), true);
    ?>
    <ul class="sp-field-settings">
      <li>
        Label: <input type="text" placeholder="Label" value="<?= $values['label'] ?>">
      </li>
      <li>
        Placeholder: <input type="text" placeholder="Placeholder" value="<?= $values['placeholder'] ?>">
      </li>
      <li>
        Required: <input type="checkbox"  <?= isset($values['required']) && $values['required'] ? "checked=\"checked\"" : '' ?>>
      </li>
      <input type="hidden" name="another_person_work_place" value="<?= get_option('another_person_work_place') ?>">
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

  public function another_person_blessing_html () {
    $val = json_decode(get_option('another_person_blessing'), true);
    ?>
    <div class="blessings-section">
      <button class="button button-primary" id="js-add-blessing">Add</button>

      <div class="blessings-container">
        <?php
        if ( count($val) ):
          foreach ( $val as $item ):
        ?>
        <blessing-list category="<?= $item['categoryName'] ?>" items="<?= implode('|', $item['items']) ?>"></blessing-list>
        <?php
          endforeach;
        endif;
        ?>
      </div>

      <input type="hidden" name="another_person_blessing" value="<?= esc_attr($val) ?>">
    </div>
    <?php
  }
}
