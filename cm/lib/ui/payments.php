<?php

require_once dirname(__FILE__).'/../base/util.php';

function render_payment_editor() {
	echo '<input type="hidden" name="edit-id" class="edit-id">';
	echo '<tr>';
		echo '<th><label for="edit-name">Payment For:</label></th>';
		echo '<td><input type="text" name="edit-name" class="edit-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-description">Description:</label></th>';
		echo '<td><textarea name="edit-description" class="edit-description"></textarea></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-first-name">First Name:</label></th>';
		echo '<td><input type="text" name="edit-first-name" class="edit-first-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-last-name">Last Name:</label></th>';
		echo '<td><input type="text" name="edit-last-name" class="edit-last-name"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-email-address">Email Address:</label></th>';
		echo '<td><input type="email" name="edit-email-address" class="edit-email-address"></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label for="edit-payment-status">Payment Status:</label></th>';
		echo '<td><select name="edit-payment-status" class="edit-payment-status">';
			echo '<option value="Incomplete">Incomplete</option>';
			echo '<option value="Cancelled">Cancelled</option>';
			echo '<option value="Completed">Completed</option>';
			echo '<option value="Refunded">Refunded</option>';
			echo '<option value="Pulled">Badge Pulled</option>';
		echo '</select></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label for="edit-payment-type">Payment Type:</label></th>';
		echo '<td><input type="text" name="edit-payment-type" class="edit-payment-type"></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label for="edit-payment-txn-id">Transaction ID:</label></th>';
		echo '<td><input type="text" name="edit-payment-txn-id" class="edit-payment-txn-id"></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th><label for="edit-payment-price">Payment Amount:</label></th>';
		echo '<td><input type="number" name="edit-payment-price" class="edit-payment-price" min="0" step="0.01"></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label for="edit-payment-date">Payment Date:</label></th>';
		echo '<td><input type="datetime-local" name="edit-payment-date" class="edit-payment-date"></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label for="edit-payment-details">Payment Details:</label></th>';
		echo '<td><textarea name="edit-payment-details" class="edit-payment-details"></textarea></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add">';
		echo '<th><label>Lookup Key:</label></th>';
		echo '<td>';
			echo '<span class="edit-payment-lookup-key-value">Not Set</span>';
			echo '<br><label><input type="radio" name="edit-payment-lookup-key" class="edit-payment-lookup-key-keep" value="keep">Keep As-Is</label>';
			echo '&nbsp;&nbsp;<label><input type="radio" name="edit-payment-lookup-key" class="edit-payment-lookup-key-clear" value="clear">Clear</label>';
			echo '&nbsp;&nbsp;<label><input type="radio" name="edit-payment-lookup-key" class="edit-payment-lookup-key-new" value="new">Generate New Key</label>';
		echo '</td>';
	echo '</tr>';
	echo '<tr class="hide-on-add edit-confirm-payment-url">';
		echo '<th><label>Confirmation &amp; Payment Link:</label></th>';
		echo '<td><a href="#" target="_blank" class="edit-confirm-payment-url"></a></td>';
	echo '</tr>';
	echo '<tr class="hide-on-add edit-review-order-url">';
		echo '<th><label>Review Order Link:</label></th>';
		echo '<td><a href="#" target="_blank" class="edit-review-order-url"></a></td>';
	echo '</tr>';
}