<?php
/**
 * External vars:
 *
 *
 * branch: [
 *   schedule_array: [
 *     day: [
 *       slots: [[from, to], ...],
 *       preparationTime: '24'
 *     ]
 *   ]
 * ]
 */
  $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
?>

<details class="delivery-branch">
  <summary class="delivery-branch__name">Address 1</summary>

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
</details>