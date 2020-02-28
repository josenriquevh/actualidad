$(document).ready(function() {

	$('#div-active-alert').hide();

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#tipopagina_id').val("");
		$('#tipopagina').val("");
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});
 
 	$('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

	$('.edit').click(function(){
		var tipopagina_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { tipopagina_id: tipopagina_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#tipopagina_id').val(tipopagina_id);
				$('#tipopagina').val(data.nombre);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$('.delete').click(function(){
		var tipopagina_id = $(this).attr('data');
        sweetAlertDelete(tipopagina_id,'EaTipoPagina');
	});

	$('#form').safeform({
		submit: function(e) {
			
			$('#div-alert').hide();
			if ($("#form").valid())
			{
				$('#guardar').prop('disabled', true);
				$.ajax({
					type: "POST",
					url: $('#form').attr('action'),
					async: true,
					data: $("#form").serialize(),
					dataType: "json",
					success: function(data) {
						$('.form-control').val('');
						$('#p-nombre').html(data.nombre);
						console.log('Formulario enviado. Id '+data.id);
						$( "#detail-edit" ).attr( "data", data.id );
						if (data.delete_disabled != '') 
						{
							$("#detail-delete").hide();
							$("#detail-delete").removeClass( "delete" );
						}
						else
						{
							$( "#detail-delete" ).attr("data",data.id);
							$( "#detail-delete" ).addClass("delete");
							$( "#detail-delete" ).show();
							$('.delete').unbind('click');
							$('.delete').click(function()
							{
								var tipopagina_id= $(this).attr('data');
		                        sweetAlertDelete(tipopagina_id,'EaTipoPagina');
							});
						}
						
						initModalShow();

						// manual complete, reenable form ASAP
						$('#form').safeform('complete');
						return false; // revent real submit
								
					},
					error: function(){
						$('#alert-error').html($('#error_msg-save').val());
						$('#div-alert').show();
						$('#guardar').prop('disabled', false);
						$('#form').safeform('complete');
                        return false; // revent real submit
					}
				});
			}
			else {
				$('#form').safeform('complete');
                return false; // revent real submit
			}
			
		}
	});

	disableSubmit();

});
