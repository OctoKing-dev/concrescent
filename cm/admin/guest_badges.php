<?php

require_once dirname(__FILE__).'/admin.php';
require_once dirname(__FILE__).'/../lib/dal/lists.php';
require_once dirname(__FILE__).'/../lib/ui/lists.php';
require_once dirname(__FILE__).'/../lib/ui/guests.php';

$conn = get_db_connection();
db_require_table('guest_badges', $conn);
db_require_table('guests', $conn);

function render_guest_badges($connection) {
	$results = mysql_query('SELECT * FROM '.db_table_name('guest_badges').' ORDER BY `order`', $connection);
	while ($result = mysql_fetch_assoc($results)) {
		$result = decode_guest_badge($result);
		$accepted = get_accepted_guest_badge_count($result['id'], $connection);
		echo render_list_row(
			array(
				$result['name'],
				array('html' => date_range_string($result['start_date'], $result['end_date'])),
				array('class' => 'numeric', 'value' => $accepted),
				array('class' => 'numeric', 'value' => ($result['count'] ? ($result['count'] - $accepted) : 'unlimited')),
				array('class' => 'numeric', 'value' => ($result['count'] ? $result['count'] : 'unlimited')),
			),
			array(
				'ea-id' => $result['id'],
				'ea-name' => $result['name'],
				'ea-description' => $result['description'],
				'ea-start-date' => $result['start_date'],
				'ea-end-date' => $result['end_date'],
				'ea-count' => $result['count'],
				'ea-active' => $result['active'],
				'ea-max-supporters' => $result['max_supporters'],
				'ea-order' => $result['order'],
			),
			/*  selectable = */ false,
			/*  switchable = */ true,
			/*      active = */ $result['active'],
			/*  deleteable = */ true,
			/* reorderable = */ true,
			/*        edit = */ true,
			/*      review = */ false
		);
	}
}

if (isset($_POST['action'])) {
	$id = (int)$_POST['id'];
	switch ($_POST['action']) {
		case 'activate': activate_entity('guest_badges', $id, $conn); break;
		case 'deactivate': deactivate_entity('guest_badges', $id, $conn); break;
		case 'delete': delete_entity('guest_badges', $id, $conn); break;
		case 'reorder': reorder_entities('guest_badges', $id, (int)$_POST['direction'], $conn); break;
		case 'save': upsert_ordered_entity('guest_badges', $id, encode_guest_badge($_POST), $conn); break;
	}
	render_guest_badges($conn);
	exit(0);
}

render_admin_head('Guest Badges');

echo '<script type="text/javascript" src="' . htmlspecialchars(resource_file_url('cmlists.js')) . '"></script>';
?><script type="text/javascript">listPage({
	ajaxUrl: 'guest_badges.php',
	switchable: true,
	deleteable: true,
	reorderable: true,
	editDialog: true,
	editDialogTitle: 'Edit Badge Type',
	editDialogStart: function(self, id, name) {
		$('.edit-id').val(id);
		$('.edit-name').val(name);
		$('.edit-description').val(self.find('.ea-description').val());
		$('.edit-start-date').val(self.find('.ea-start-date').val());
		$('.edit-end-date').val(self.find('.ea-end-date').val());
		$('.edit-count').val((1 * self.find('.ea-count').val()) || '');
		$('.edit-active').attr('checked', !!self.find('.ea-active').val());
		$('.edit-max-supporters').val((1 * self.find('.ea-max-supporters').val()) || '');
	},
	addDialog: true,
	addDialogTitle: 'Add Badge Type',
	addDialogStart: function() {
		$('.edit-id').val('');
		$('.edit-name').val('');
		$('.edit-description').val('');
		$('.edit-start-date').val('');
		$('.edit-end-date').val('');
		$('.edit-count').val('');
		$('.edit-active').attr('checked', true);
		$('.edit-max-supporters').val('');
	},
	addEditDialogGetSaveData: function(id, name) {
		return {
			'id': id,
			'name': name,
			'description': $('.edit-description').val(),
			'start_date': $('.edit-start-date').val(),
			'end_date': $('.edit-end-date').val(),
			'count': $('.edit-count').val(),
			'active': ($('.edit-active').attr('checked') ? 1 : 0),
			'max_supporters': $('.edit-max-supporters').val(),
		};
	},
});</script><?php

render_admin_body('Guest Badges');

echo '<div class="card entity-list-card">';
render_list_table(array(
	'Name', 'Dates Available',
	array('class' => 'numeric', 'name' => '# Accepted'),
	array('class' => 'numeric', 'name' => '# Left'),
	array('class' => 'numeric', 'name' => '# Total'),
), 'render_guest_badges', true, $conn);
echo '</div>';

render_admin_dialogs();

render_delete_dialog('guest badge', true);

render_edit_dialog_start();
render_guest_badge_editor();
render_edit_dialog_end();

render_admin_tail();