$(document).ready(function() {

	var notificacion_programada_id = $('#notificacion_programada_id').val();
	if (notificacion_programada_id)
	{
		var tipo_destino_id = $('#tipo_destino_id').val();
		grupoSeleccion(notificacion_programada_id, tipo_destino_id);
	}

	$('#fecha_difusion').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('#tipo_destino_id').change(function(){
		var tipo_destino_id = $('#tipo_destino_id').val();
		var notificacion_programada_id = $('#notificacion_programada_id').val();
		if (tipo_destino_id != '')
		{
			grupoSeleccion(notificacion_programada_id, tipo_destino_id);
		}
	});

	
});

function grupoSeleccion(notificacion_programada_id, tipo_destino_id)
{
	$('#change').show();
	$('.load1').show();
	$('#div-entidades-alert').hide();
	$('.div-grupo').hide();
	$.ajax({
		type: "GET",
		url: $('#url_grupo').val(),
		async: true,
		data: { tipo_destino_id: tipo_destino_id, notificacion_programada_id: notificacion_programada_id },
		dataType: "json",
		success: function(data) {
			$('#div-entidades').html(data.html);
			if (tipo_destino_id == 6 || tipo_destino_id == 7)
			{
				observeMultiSelect();
			}
			else {
				observe();
			}
		},
		error: function(){
			$('.load1').hide();
			$('#div-entidades-alert').show();
		}
	});
}

function observe()
{

	$('#provincia_id').change(function(){
        var provincia_id = $('#provincia_id').val();
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var reference = $(this).attr('reference');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        if (provincia_id != '')
        {
			selectDependiente(provincia_id, entity, field_update, reference, orderBy);
        }
	});
	
	$('#ciudad_id').change(function(){
		var ciudad_id = $('#ciudad_id').val();
		var colegio = $(this).attr('data');
		$('#'+colegio).hide();
		$('#loader-'+colegio).show();
		resetSelects(colegio);
        if (ciudad_id != '')
        {
			$.ajax({
				type: "GET",
				url: $('#url_colegio').val(),
				async: true,
				data: { ciudad_id: ciudad_id},
				dataType: "json",
				success: function(data) {
					$('#colegiob').html(data.options);
					$('#'+colegio).html(data.options);
					$('#'+colegio).show();
					$('#loader-'+colegio).hide();
					observeSelectChosen();
				},
				error: function(){
					$('#div-error-server').html($('#error-msg-seccion_id').val());
		            notify($('#div-error-server').html());
				}
			});
        }
	});

	$('#colegio_id, #grado_id').change(function(){
        var colegio_id = $('#colegio_id').val();
		var grado_id = $('#grado_id').val();
		if (grado_id != '' && colegio_id != '')
		{
			$('#entidades').hide();
			$('#loader-entidades').show();
		    $.ajax({
				type: "GET",
				url: $('#url_seccion').val(),
				async: true,
				data: { colegio_id: colegio_id, grado_id: grado_id},
				dataType: "json",
				success: function(data) {
					$('#entidades').html(data.options);
					$('#loader-entidades').hide();
					$('#entidades').show();
				},
				error: function(){
					$('#div-error-server').html($('#error-msg-seccion_id').val());
		            notify($('#div-error-server').html());
				}
			});
		}
	});

	$('#grado_id_l').change(function(){
		var grado_id_l = $('#grado_id_l').val();
		$('#entidades').hide();
		$('#loader-entidades').show();
        $.ajax({
			type: "GET",
			url: $('#url_libro').val(),
			async: true,
			data: { grado_id_l: grado_id_l},
			dataType: "json",
			success: function(data) {
				$('#entidades').html(data.options);
				$('#loader-entidades').hide();
				$('#entidades').show();
			},
			error: function(){
				$('.load1').hide();
				$('#div-entidades-alert').show();
			}
		});
	});

}
