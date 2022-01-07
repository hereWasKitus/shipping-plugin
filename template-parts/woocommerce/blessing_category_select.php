<?php
$blessings = json_decode(get_option('another_person_blessing'), true);
?>

<?php if ( count( $blessings ) ): ?>

<div class="form-row">
  <label>Blessing category</label>
  <!-- Select -->
  <select class="js-blessing-category">
    <option value="" disabled selected>Choose category</option>
    <?php foreach ( $blessings as $blessing ): ?>
      <option value="<?= $blessing['categoryName'] ?>"><?= $blessing['categoryName'] ?></option>
    <?php endforeach; ?>
  </select>

  <!-- Popup -->
  <div class="blessing-popup">
    <div class="blessing-popup-inner">
      <div class="blessing-popup-header">
        <p>בחר נוסח לברכה מהמאגר</p>
      </div>

      <ul class="blessing-popup-list" data-modal-list></ul>

      <div class="blessing-popup-footer">
        <button data-modal-close>סגור</button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>