$(document).ready(function() {

	segundaTabla();
	$('#formFiltro').submit(function(e) {
		e.preventDefault();
	});
	$('#search').click(function(){
		var valid = $("#formFiltro").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#load1').show();
        	$('#load2').show();
        	$('#list_comentarios').hide();
        	$('#tbody_history_programation').hide();
        	$("html,body").animate({scrollTop: $('#listaRespuestas').offset().top}, 500);
        	$('#formFiltro').submit();
			return false;	
        }
	});

	 $('#formFiltro').safeform({    
		submit: function(e) {
			
			$.ajax({
				type: "POST",
				url: $('#formFiltro').attr('action'),
				async: true,
				data: $("#formFiltro").serialize(),
				dataType: "json",
				success: function(data) {
					if(data.ok==1){
						$('#list_comentarios').html(data.temas);
						$('#tbody_history_programation').html(data.comentarios);
						$('#load1').hide();
						$('#list_comentarios').show();
						$('#load2').hide();
						$('#tbody_history_programation').show();
						applyDataTable();
						segundaTabla();
					}
					else{
						notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					}
					$('#formFiltro').safeform('complete');
                    return false;
				},
				error: function(){
					$('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
					notify('','danger',$('#div-error').html());
					$('#formFiltro').safeform('complete');
                    return false;
				}
			});
		}
	});

	$('#libro_id').change(function(){
		var libro_id = $(this).val();
		$('#img-loader-unidad').show();
		$('#unidad_id').html('');
		  $.ajax({
				type: "POST",
				url: $('#url_unidades_foro').val(),
				async: true,
				data: {libro_id: libro_id },
				dataType: "json",
				success: function(data) {
					if(data.ok==1){
						$('#img-loader-unidad').hide();
						$('#unidad_id').html('');
						if(data.cnt == 0){
							notify('','warning','<b>'+$('#alert-msg-Udisponibles').val()+'</b>');
						}
						else{
							$('#unidad_id').html(data.unidades);
						}
						
					}
					else{
						notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					}
					
				},
				error: function(){
					$('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
					notify('','danger',$('#div-error').html());
				}
			});

	});

	$('.selDepend').change(function(){
		var dependId = $(this).attr('data-depend');
		var dependVal = $('#'+dependId).val();
		$('#unidad_id').html('');
		
		if ( dependVal !="" && $(this).val()!=""){
		    var empresa_id = $('#empresa_id').val();
		    var grado_id = $('#grado_id').val();
		    $('#img-loader-libro').show();
		    $.ajax({
				type: "POST",
				url: $('#url_libros_foro').val(),
				async: true,
				data: {empresa_id: empresa_id, grado_id: grado_id },
				dataType: "json",
				success: function(data) {

					if(data.ok==1){
						$('#libro_id').html('');
						$('#img-loader-libro').hide();
						if(data.cnt == 0){
							notify('','warning','<b>'+$('#alert-msg-Ldisponibles').val()+'</b>');
						}
						else{
							$('#libro_id').html(data.libros);
						}
					}
					else{
						notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					}
					
				},
				error: function(){
					$('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
					notify('','danger',$('#div-error').html());
				}
			});
		}
		
	});


	observe();
});

function observe(){

	$('#list_comentarios .delete').off('click');
	$('#temasComentarios ').on('click', '.delete', function(){
		var comentario_id = $(this).attr('data');
		sweetAlertDelete(comentario_id, 'EaForo');
	});

	$('#list_comentarios .see').off('click');
    $('#list_comentarios').on('click', '.see', function(){
    	var foro_id = $(this).attr('data');
    	$("html,body").animate({scrollTop: $('#listaRespuestas').offset().top}, 500);
    	$('#tbody_history_programation').hide();
        $('#load2').show();
    	$.ajax({
				type: "POST",
				url: $('#url_comentarios_foro').val(),
				async: true,
				data: { foro_id: foro_id },
				dataType: "json",
				success: function(data) {
					if(data.ok == 1)
					{
	       				$('#load2').hide();
						$('#tbody_history_programation').html(data.comentarios);
						$('#tbody_history_programation').show();
						segundaTabla();
						linkArchivos();
					}
					else{
						notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					}

				},
				error: function(){
					$('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
					notify('','danger',$('#div-error').html());
				}
			});

	 });
}




function linkArchivos()
{
	$( ".fileList").unbind( "click" );

	$('.fileList').click(function()
	{
			$('#img-loader-files').show();
			var comentarioId = $(this).attr('data-comentario');
			$.ajax({
			type: "POST",
			url: $('#url_files_foroList').val(),
			async: true,
			data: $('#comentario'+comentarioId).serialize(),
			dataType: "json",
			success: function(data) {
				if(data.ok==1)
				{
					title(data.usuario);
					$('#listOfFiles').html(data.html);
					$('#img-loader-files').hide();
					$('#filesModal').modal('show');
				}
				else{
						notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					}
				
			},
			error: function(){
				$('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
				notify('','danger',$('#div-error').html());
				
			}
		});
		
	});
}

function segundaTabla()
{
	var table2 = $('#dtSub').DataTable( {
        responsive: false,
        pageLength:10,
        destroy: true,
        sPaginationType: "full_numbers",
        oLanguage: {
        	"sProcessing":    "Procesando...",
            "sLengthMenu":    "Mostrar _MENU_ registros",
            "sZeroRecords":   "No se encontraron resultados",
            "sEmptyTable":    "Ningún dato disponible en esta tabla",
            "sInfo":          "Mostrando registros del _START_ al _END_ de un total de _TOTAL_.",
            "sInfoEmpty":     "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":  "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":   "",
            "sSearch":        "Buscar:",
            "sUrl":           "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            oPaginate: {
                sFirst: "<<",
                sPrevious: "<",
                sNext: ">", 
                sLast: ">>" 
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
        }

    } );

    table2.on( 'row-reorder', function ( e, diff, edit ) {
        
        for ( var i=0, ien=diff.length ; i<ien ; i++ ) {
            var rowData = table2.row( diff[i].node ).data();
            // Id del registro está en la segunda columna
        	id = rowData[1];
            reordenar(id, 'CertiForo', diff[i].newData);
        } 
    });

}



