$(document).ready(function() {

	$('#div-active-alert').hide();

    $('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
    });

	$('.new,.edit').click(function(){
		limpiar_campos()
	});

	observe();

	$('#form').safeform({
		submit: function(e) {
			$('#div-alert').hide();
			$('#guardar').hide();
			var valid = $("#form").valid();
		    if (!valid) 
		    {
		        notify($('#div-error').html());
		        $('#guardar').show();
		        $('#form').safeform('complete');
                return false; // revent real submit
		    }
		    else {
		        var pregunta_opcion_id = $('#pregunta_opcion_id').val()
				$.ajax({
					type: "POST",
					url: $('#form').attr('action'),
					async: true,
					data: $("#form").serialize()+'&es_asociacion='+$('#es_asociacion').val(),
					dataType: "json",
					success: function(data) {
						$('.form-control').val('');
						if (pregunta_opcion_id)
						{
							$( "#tr-"+pregunta_opcion_id ).html( data.html );
						}
						else {
							$( "#tbody-options" ).append( data.html );
						}
						// Si se marca SI, las demás deben marcarse como NO
						if (data.checked != '' && $('#es_simple').val() == 1)
						{
							$('.cb_activo').each(function(){
								if ($(this).attr('id') != 'f'+data.id)
								{
									$(this).prop('checked', false);
								}
							});
						}
						observe();
						$( "#cancelar" ).trigger( "click" );
						clearTimeout( timerId );
					},
					error: function(){
						$('#alert-error').html($('#error_msg-save').val());
						$('#div-alert').show();
						$('#guardar').show();
						$('#form').safeform('complete');
                        return false; // revent real submit
					}
				});
		    }    
		}
	});

	$('.btn_addImg').click(function(){
    	var a_data = $(this).attr('data');
    	$('#file_input').val(a_data);
    	$('#div-error ul').hide();
    	$('#div-error ul').html('');
    });

    $('.fileupload').fileupload({
        url: $('#url_upload').val(),
        dataType: 'json',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        var file_input = $('#file_input').val();
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && (file_input == 'imagen' || file_input == 'imagen_enunciado') ) {
	        	$('#div-error ul').html("<li>- Debes seleccionar sólo archivo de imagen</li>");
	            goUpload = false;
	        }
	        if (goUpload == true) {
	            data.submit();
	        }
	        else {
	        	$('#div-error ul').show();
                notify($('#div-error').html());
	        }
	    },
        done: function (e, data) {
        	$.each(data.result.response.files, function (index, file) {
        		var file_input = $('#file_input').val();
        		var uploads = $('#uploads').val();
        		var base_upload = $('#base_upload').val();
                if (file_input == 'imagen')
                    $("#figure").html('<img id="foto_img" src="'+uploads+base_upload+file.name+'" alt="" class="img_figure">');
        		else
                    $("#figure_imagen_enunciado").html('<img id="foto_img" src="'+uploads+base_upload+file.name+'" alt="" class="img_figure">');
        	
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
    });

	disableSubmit();

});

function limpiar_campos()
{	
	$('#div-alert').hide();
	$('#pregunta_opcion_id').val("");
	$('#descripcion').val("");
	$('#enunciado').val("");
	$('#imagen').val("");
	$('#figure').html('<img src="'+$('#img_default').val()+'" class="img_figure">');
	$('#imagen_enunciado').val("");
	$('#figure_imagen_enunciado').html('<img src="'+$('#img_default').val()+'" class="img_figure">');
	enableSubmit();	
}
function observe()
{
	var es_simple = $('#es_simple').val();
	var es_asociacion = $('#es_asociacion').val();
	var elemento_imagen = $('#elemento_imagen').val();

	$('.edit').unbind('click');
	$('.edit').click(function(){
		var pregunta_opcion_id = $(this).attr('data');
		$('#div-alert').hide();
		$.ajax({
			type: "GET",
			url: $('#url_edit').val(),
			async: true,
			data: { pregunta_opcion_id: pregunta_opcion_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#pregunta_opcion_id').val(pregunta_opcion_id);
				$('#descripcion').val(data.descripcion);
				if (elemento_imagen == 1)
				{
					var imagen='';
					var imagen_enunciado='';
	        		var uploads = $('#uploads').val();

					if(data.imagen!=null)
						imagen=uploads+data.imagen;
					else
						imagen=$('#img_default').val();

					$('#imagen').val(data.imagen);
                    $("#figure").html('<img id="foto_img" src="'+imagen+'" alt="" class="img_figure">');

					if (es_asociacion == 1)
					{
						if(data.imagen_enunciado!=null)
							imagen_enunciado=uploads+data.imagen_enunciado;
						else
							imagen_enunciado=$('#img_default').val();

						$('#imagen_enunciado').val(data.imagen_enunciado);
                    	$("#figure_imagen_enunciado").html('<img id="foto_img" src="'+imagen_enunciado+'" alt="" class="img_figure">');
					}
				}
				if (es_asociacion == 0)
					$('#correcta').prop('checked', data.correcta);
				else 
					$('#enunciado').val(data.enunciado);
				
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	if (es_asociacion == 0 )
	{
		$('.cb_activo').unbind('click');
		$('.cb_activo').click(function(){
			var checked = $(this).is(':checked') ? 1 : 0;
			var id = $(this).attr('id');
			var id_arr = id.split('f');
			var pregunta_opcion_id = id_arr[1];
			$('#div-alert').hide();
			// Si se marca SI, las demás deben marcarse como NO
			if (checked == 1)
			{
				$('.cb_activo').each(function(){
					if ($(this).attr('id') != id )
					{
						$(this).prop('checked', false);
					}
				});
			}
			$.ajax({
				type: "POST",
				url: $('#url_correcta').val(),
				async: true,
				data: { pregunta_opcion_id: pregunta_opcion_id, checked: checked },
				dataType: "json",
				success: function(data) {
					console.log('Activación/Desactivación realizada. Id '+data.id);
					clearTimeout( $('#timerId').val() );
					activarTimeout();
				},
				error: function(){
					$('#active-error').html($('#error_msg-active').val());
					$('#div-active-alert').show();
				}
			});
		});
	}

	$('.delete').unbind('click');
	$('.delete').click(function(){
		var pregunta_opcion_id = $(this).attr('data');
		sweetAlertDelete(pregunta_opcion_id, 'EaPreguntaOpcion', $('#url_delete_opcion').val());
	});

}

