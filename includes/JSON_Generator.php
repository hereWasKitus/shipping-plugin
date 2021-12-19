<?php
class JSON_Generator {
  public function page_html() {
  ?>
    <h1>JSON generator</h1>

    <form class="json-generator-form">
      <input type="number" name="order_id" placeholder="<?= __('Enter order ID') ?>">
      <button class="button button-primary">Generate order JSON</button>
    </form>

    <p class="json-generator-error"></p>

    <a style="display: none;" class="js-download-json button button-primary" href="/" download>Download</a>
  <?php
  }
}