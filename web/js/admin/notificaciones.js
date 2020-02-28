$(document).ready(function() {

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var notificacion_id = $(this).attr('data');
		sweetAlertDelete(notificacion_id, 'AdminNotificacion');
	});

});
