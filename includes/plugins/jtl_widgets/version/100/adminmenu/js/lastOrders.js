$(document).ready(function() {
	$('#table_last_orders tr').click(function() {
		var id = $(this).attr('id'),
			orderkey = id.replace('last_order_row_', '');
		$('#last_order_pop_' + orderkey).dialog({draggable : false, width: 550, resizable: false, modal: true, closeText: 'CLOSE', title: $('#' + id).attr('title')});
	});
});