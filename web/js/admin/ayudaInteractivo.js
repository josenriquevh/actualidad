$(document).ready(function() {

	$('.new').click(function(){
		initModalEdit();
		enableSubmit();
		$('#ayuda_interactivo_id').val("");
		$('#nombre').val("");
		$('#gif').val("");
		$('#mensaje').val("");
		$('#wait').hide();
	});

	$('#guardar').click(function(){
		$('#form').submit();
		return false;
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});

	$('#form').safeform({
		submit: function(e) {

			$('#div-alert').hide();
			$('#div-error').hide();
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
						if($('#ayuda_interactivo_id').val() != '')//si se edita un tutorial
						{
							table.ajax.reload(null,false);//recarga los datos de la tabla manteniendose en la pagina actual
						}
						else {
							table.ajax.reload(null,true)//recarga los datos de la tabla y la muestra desde la pagina inicial
						}
						$('#p-nombre').html(data.nombre);
						$('#p-gif').html(data.gif);
						$('#p-mensaje').html(data.mensaje);
						$( "#detail-edit" ).attr( "data", data.id );
						$( "#detail-delete" ).attr("data",data.id);
						$( "#detail-edit" ).attr( "disabled", false);
						$( "#detail-delete" ).attr("disabled", false);
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
				$('#div-error').show();
				$('#form').safeform('complete');
                return false; // revent real submit
			}
		}
	});

	$('.uploadFileHref').click(function(){
		$('#fileUpload').val($(this).attr('data-etiqueta'));
		$('#div-error').hide();
	});

	$('.uploadFile').fileupload({
		url: $('#url_uploadFiles_AyudaInteractivo').val(),
        dataType: 'json',
        done: function (e, data) {
        	var id = $('#fileUpload').val();
        	$.each(data.result.response.files, function (index, file) 
        	{
        		$('#'+id).val(file.name);
            });
            showButtons();
            $('#div-error').hide();
        },
        add: function (e,data ){
        	 hideButtons();
	  		 data.submit();
        },
        fail: function(e, data){
        	failedRequest();
        }
    });

	$('.form-control').focus(function(){
		$('#div-alert').hide();
		$('#div-error').hide();
		$('.form-control').removeClass('error');
	});

	$( "#BodyTable, #buttons" ).on( "click",".edit" , function (){
		document.getElementById("form").reset();
		var ayuda_interactivo_id = $(this).attr('data');
		var url_edit = $('#url_edit').val();
		initModalEdit();
		$('#div-error').hide();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { ayuda_interactivo_id: ayuda_interactivo_id },
			dataType: "json",
			success: function(data) {
				enableSubmit();
				$('#ayuda_interactivo_id').val(ayuda_interactivo_id);
				$('#nombre').val(data.nombre);
				$('#gif').val(data.gif);
				$('#mensaje').val(data.mensaje);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

	$( "#BodyTable, #buttons" ).on( "click",".delete" , function (){
		var ayuda_interactivo_id = $(this).attr('data');
		var ubicacion = $(this).attr('data-ubicacion');
		sweetAlertDeleteAyudaInteractivo(ayuda_interactivo_id,ubicacion);	
     });

});

function showButtons()
{
	$('#guardar').show();
    $('#cancelar').show();
    $('#wait').hide();
	$('.uploadFileHref').show();
	return 0;
}

function hideButtons()
{
	$('.uploadFileHref').hide();
    $('#guardar').hide();
	$('#cancelar').hide();
	$('#wait').show(1000);
	return 0;
}
