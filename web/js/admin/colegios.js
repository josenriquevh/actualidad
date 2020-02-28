$(document).ready(function() {

    $('#provincia_id').change(function(){
        var provincia_id = $('#provincia_id').val();
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var reference = $(this).attr('reference');
        var orderBy = $(this).attr('orderBy');
        $('#new_colegio').hide();
        resetSelects(field_update);
        if (provincia_id != '')
        {
            $('#div-paginas').hide();
            selectDependiente(provincia_id, entity, field_update, reference, orderBy);
        }
    });

    $('#ciudad_id').change(function(){
        var ciudad_id = $('#ciudad_id').val();
        var provincia_id = $('#provincia_id').val();
        var colegio_nombre = $('#colegio_nombre').val();
        $('#div-paginas').show();
        $('#listado').hide();
        $('.load1').show();
        $.ajax({
            type: "GET",
            url: $("#url_buscar").val(),
            async: true,
            data: { ciudad_id: ciudad_id, provincia_id: provincia_id, colegio_nombre: colegio_nombre },
            dataType: "json",
            success: function(data) {
                $('#panelTitle').html(data.header);
                $('#listado').html(data.html);
                $('#listado').show();
                $('.load1').hide();
                $('#new_colegio').show();
                applyDataTableReorder();
                $('.delete').click(function(){
                    var colegio_id = $(this).attr('data');
                    sweetAlertDelete(colegio_id,'AdminColegio');
                });
                $('.edit').click(function(){
                    var colegio_id = $(this).attr('data');
                    var url_edit = $('#url_edit').val();
                    $('#nombre').val("");
                    initModalEdit();
                    $.ajax({
                        type: "GET",
                        url: url_edit,
                        async: true,
                        data: { colegio_id: colegio_id },
                        dataType: "json",
                        success: function(data) {
                            enableSubmit();
                            $('#colegio_id').val(colegio_id);
                            $('#nombre').val(data.nombre);
                            $('.paginate_button').click(function(){
                                observe();
                            });
                        },
                        error: function(){
                            $('#alert-error').html($('#error_msg-edit').val());
                            $('#div-alert').show();
                        }
                    });
                });
                $('.paginate_button').click(function(){
                    observe();
                });
            },
            error: function(){
                $('#div-error-server').html('Error obteniendo la lista de colegios');
                notify($('#div-error-server').html());
            }
        });
    });

    $('.new').click(function(){
        var provincia_id = $('#provincia_id').val();
        var ciudad_id = $('#ciudad_id').val();
		initModalEdit();
		enableSubmit();
		$('#colegio_id').val("");
        $('#nombre').val("");
        $('#m_provincia_id').val(provincia_id);
        $('#m_ciudad_id').val(ciudad_id);
    });
    
    $('#aceptar').click(function(){
		window.location.replace($('#url_list').val());
	});

    applyDataTableReorder();

    $("#form").validate({
        rules: {
            'nombre': {
                required: true,
                minlength: 3
            }
        },
        messages: {
            'nombre': {
                required: "Este campo es requerido",
                minlength: "Debe ser mínimo de 3 caracteres"
            }
        }
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
								var colegio_id= $(this).attr('data');
		                        sweetAlertDelete(colegio_id,'AdminColegio');
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
    
    $('#colegioB').click(function(){
        
        $('#filtro1').hide();
        $('#filtro2').show();
        $('#provincia_id').val("");
        $('#ciudad_id').val("");
        $('#div-paginas').hide();
        $('#new_colegio').hide();
    });

    $('#colegioB1').click(function(){
        
        $('#filtro2').hide();
        $('#filtro1').show();
        $('#colegio_nombre').val("");
        $('#div-paginas').hide();
        $('#new_colegio').hide();
        
    });

    $('#search').click(function(){
        var colegio_nombre = $('#colegio_nombre').val();
        var provincia_id = $('#provincia_id').val();
        var ciudad_id = $('#ciudad_id').val();
        $('#div-paginas').show();
        $('#listado').hide();
        $('.load1').show();
        $.ajax({
            type: "GET",
            url: $("#url_buscar").val(),
            async: true,
            data: { colegio_nombre: colegio_nombre, ciudad_id: ciudad_id, provincia_id: provincia_id},
            dataType: "json",
            success: function(data) {
                $('#panelTitle').html(data.header);
                $('#listado').html(data.html);
                $('#listado').show();
                $('.load1').hide();
                applyDataTableReorder();
                $('.delete').click(function(){
                    var colegio_id = $(this).attr('data');
                    sweetAlertDelete(colegio_id,'AdminColegio');
                });
                $('.edit').click(function(){
                    var colegio_id = $(this).attr('data');
                    var url_edit = $('#url_edit').val();
                    $('#nombre').val("");
                    initModalEdit();
                    $.ajax({
                        type: "GET",
                        url: url_edit,
                        async: true,
                        data: { colegio_id: colegio_id },
                        dataType: "json",
                        success: function(data) {
                            enableSubmit();
                            $('#colegio_id').val(colegio_id);
                            $('#nombre').val(data.nombre);
                            $('.paginate_button').click(function(){
                                observe();
                            });
                        },
                        error: function(){
                            $('#alert-error').html($('#error_msg-edit').val());
                            $('#div-alert').show();
                        }
                    });
                });
                $('.paginate_button').click(function(){
                    observe();
                });
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-paginas').val());
                notify($('#div-error-server').html());
            }
        });
    });

});

function observe()
{
    console.log('entrando en observe');
    $('.edit').unbind('click');
    $('.edit').click(function(){
		var colegio_id = $(this).attr('data');
        var url_edit = $('#url_edit').val();
        var provincia_id = $('#provincia_id').val();
        var ciudad_id = $('#ciudad_id').val();
        $('#nombre').val("");
		initModalEdit();
		$.ajax({
			type: "GET",
			url: url_edit,
			async: true,
			data: { colegio_id: colegio_id, provincia_id: provincia_id, ciudad_id: ciudad_id },
			dataType: "json",
			success: function(data) {
                enableSubmit();
				$('#colegio_id').val(data.id);
				$('#nombre').val(data.nombre);
			},
			error: function(){
				$('#alert-error').html($('#error_msg-edit').val());
				$('#div-alert').show();
			}
		});
	});

    $('.delete').unbind('click');
	$('.delete').click(function(){
		var colegio_id = $(this).attr('data');
        sweetAlertDelete(colegio_id,'AdminColegio');
	});
}