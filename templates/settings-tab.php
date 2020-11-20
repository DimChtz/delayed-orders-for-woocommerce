<?php

	global $wcdo_settings;

	$unmarked = wcdo_count_unmarked_orders($wcdo_settings['delay_order_statuses']);

?>

<h2><?php _e('Update Delay Status', 'wc-delayed-orders'); ?></h2>
<?php if ( $unmarked > 0 ): ?>
	<p><?php _e('We found', 'wc-delayed-orders'); ?> <strong><?= $unmarked ?> <?php _e('order(s)', 'wc-delayed-orders'); ?></strong> <?php _e('without a delay status. It is recommended to update all orders.', 'wc-delayed-orders'); ?></p>
<?php else: ?>
	<p><?php _e('All orders are updated.', 'wc-delayed-orders'); ?></p>
<?php endif; ?>
<button type="submit" class="button button-primary<?php if ( $unmarked == 0 ) echo ' disabled'; ?>"><?php _e('Update', 'wc-delayed-orders'); ?> <?= $unmarked; ?> <?php _e('order(s)', 'wc-delayed-orders'); ?></button><br><br>

<h2><?php _e('Basic Settings', 'wc-delayed-orders'); ?></h2>
<p><?php _e('Basic settings for delayed orders', 'wc-delayed-orders'); ?></p>

<table class="form-table">
	<tbody>

		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e('Min delay period', 'wc-delayed-orders'); ?></th>
			<td class="">
				<input type="number" name="delay_days" value="<?= $wcdo_settings['delay_days']; ?>" step="1" min="1" style="vertical-align: middle; width: 100px; height: 34px;">
				<select name="delay_days_range" style="width: 120px;">
					<option value="0" <?php if ( $wcdo_settings['delay_days_range'] == '0' ) echo 'selected="selected"'; ?>><?php _e('Day(s)', 'wc-delayed-orders'); ?></option>
					<option value="1" <?php if ( $wcdo_settings['delay_days_range'] == '1' ) echo 'selected="selected"'; ?>><?php _e('Week(s)', 'wc-delayed-orders'); ?></option>
					<option value="2" <?php if ( $wcdo_settings['delay_days_range'] == '2' ) echo 'selected="selected"'; ?>><?php _e('Month(s)', 'wc-delayed-orders'); ?></option>
					<option value="3" <?php if ( $wcdo_settings['delay_days_range'] == '3' ) echo 'selected="selected"'; ?>><?php _e('Year(s)', 'wc-delayed-orders'); ?></option>
				</select>
				<p><?php _e('The minimum period to consider an order delayed.', 'wc-delayed-orders'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th class="titledesc" scope="row"><?php _e('Enable for statuses', 'wc-delayed-orders'); ?></th>
			<td class="">
				<input type="hidden" name="delay_order_statuses[]" value="default">
				<?php foreach ( wc_get_order_statuses() as $key => $status ): ?>
					<label>
						<input type="checkbox" name="delay_order_statuses[]" value="<?= $key; ?>" <?php if ( in_array($key, (array)$wcdo_settings['delay_order_statuses']) ) echo 'checked="checked"'; ?>>
						<?= $status; ?>
					</label><br>
				<?php endforeach; ?>
			</td>
		</tr>

	</tbody>
</table>