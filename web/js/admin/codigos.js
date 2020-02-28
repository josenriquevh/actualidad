$(document).ready(function() {
  
	 applyDataTableReorder();
	 $('#form').safeform({    
		submit: function(e) {
			waitSearch();
			$.ajax({
				type: "POST",
				url: $('#form').attr('action'),
				async: true,
				data: $("#form").serialize(),
				dataType: "json",
				success: function(data) {
					$('.load1').hide();
		        	$('#search').show();
		        	$('#panelTitle').html(data.header);
                    $('#listado').html(data.html);
                    $('#listado').show();
                    applyDataTableReorder();
					$('#form').safeform('complete');
					return false;
				},
				error: function(){
					$('#div-error-server').html('<BR>'+$('#error-msg-libros').val());
					notify($('#div-error-server').html(),'danger','<b>'+$('#error-msg').val()+'</b>');
		        	$('.load1').hide();
		        	$('#panelTitle').text('');
		        	$('#div-paginas').hide();
		        	$('#search').show();
					$('#form').safeform('complete');
                    return false;
				}
			});
		}
	});

	$('#form').submit(function(e) {
		e.preventDefault();
	});
	
	$('#filtro').on('click','#search',function(){

		var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	
        	$('#form').submit();
			return false;	
        }
	}); 

	$('#formList').submit(function(e) {
		e.preventDefault();
	});

	$('#filtroExcel').on('click','#generar-excel-btn',function(){

		var valid = $("#formList").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	
        	$('#formList').submit();
			return false;	
        }
	});

     
    $('#formCodigos').safeform({  

		submit: function(e) {
			$('#btn-process').hide();
			$('#img-loader').show();
			$.ajax({
				type: "POST",
				url: $('#formCodigos').attr('action'),
				async: true,
				data: $("#formCodigos").serialize(),
				dataType: "json",
				success: function(data) {
					$('#img-loader').hide();
					if(data.ok == 1)
					{
	                    if(data.case==1){
						  notify('','danger','<b>'+$('#error-msg-total').val()+'</b>');
						  $('#btn-process').show();
	                    }
	                    else if(data.case==2){
	                      notify('','warning','<b>'+$('#alert-msg-disponibles').val()+' '+data.disponibles+', '+$('#alert-msg-disponibles-cont').val()+'</b>');
	                      $('#btn-process').show();
	                    }
	                    else if(data.case==3){
	                       $('#inserts').text(data.codCant+' / '+$('#cantidad_ejemplares').val());
	                       $('#alert-success').show();
	                       $('#indices').val(data.indices);
	                       $('#generar-excel').show();
	                    }
					$('#formCodigos').safeform('complete');
					return false;
				 }
				 else
				 {
				 	notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					$('#btn-process').show();
					$('#formCodigos').safeform('complete');
                    return false;
				 }
				},
				error: function(){
					$('#div-error').html($('#error-msg-server').val());
					notify('','danger',$('#div-error').html());
					$('#btn-process').show();
					$('#formCodigos').safeform('complete');
                    return false;
				}
			});
		}
	});

	 $('#formList').safeform({  

		submit: function(e) {
			$('#img-loader').show();
			$('#generar-excel').hide();
			$.ajax({
				type: "POST",
				url: $('#formList').attr('action'),
				async: true,
				data: $("#formList").serialize(),
				dataType: "json",
				success: function(data) {
				$('#img-loader').hide();
				if (data.ok==1){
					$('#descargarExcel').attr('data-href',data.archivo);
					$('#inserts').text(data.cantidad);
					$('#alert-success').show();
					$('#btn-descarga').show();
				}
				else if(data.ok==2){
					$('#generar-excel').show();
					$('#div-warning-msg').html('');
				    notify('','warning','<b>'+$('#warning-msg-codigos').val()+'</b>');
				    $('#generar-excel').show();  
				}
				else if(data.ok==0)
				{
					notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
					 $('#generar-excel').show(); 
					
				}
				$('#formList').safeform('complete');
                    return false;
					
				},
				error: function(){
					$('#img-loader').hide();
					$('#generar-excel').show();
					$('#div-error').html($('#error-msg-server').val());
					notify('','danger',$('#div-error').html());
					$('#btn-process').show();
					$('#formList').safeform('complete');
                    return false;

				}
			});
		}
	});


	$('#formCodigos').submit(function(e) {
		e.preventDefault();
	});

	$('#fecha_desde').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fecha_hasta').datepicker('setStartDate', startDate);
    });
    
     $('#fecha_hasta').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fecha_desde').datepicker('setEndDate', endDate);
    });

     $('#fecha_in').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fecha_out').datepicker('setStartDate', startDate);
    });

    
    
     $('#fecha_out').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fecha_in').datepicker('setEndDate', endDate);
    });


    $('#renovable').change(function(event) {
		var checked=$(this).prop('checked');

		if (checked) 
		{
			$('#cantidad_renovaciones').attr('readonly',false);
			$('#cantidad_renovaciones').val('');
			$(this).val('TRUE');
		}
		else
		{
			$('#cantidad_renovaciones').attr('readonly',true);
			$('#cantidad_renovaciones').val(0);
			$(this).val('FALSE');
		}
		
	});

	$('#process').click(function(event) {
		var valid = $("#formCodigos").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
        	$('#formCodigos').submit();
			return false;
        }
	});

    
   $('#excel-btn').click(function(event) {

		window.location.href = $(this).attr('data-href');
		$('#btn-excel').hide();
		$('#btn-process').show();
		 reiniciarCampos();

   });

    $('#descargarExcel').click(function(event) {

		window.location.href = $(this).attr('data-href');
		$('#btn-descarga').hide();
		$('#alert-success').hide();
		$('#generar-excel').show();


   });




   $('#btn-generar-excel').click(function(){
   	  var libro_id = $('#libro_id').val();
   	  var indices =  $('#indices').val();
   	  var url = $('#excelCargaUrl').val();
   	  $('#generar-excel').hide();
   	  $('#img-loader').show();

   	  			$.ajax({
				type: "POST",
				url: url,
				async: true,
				data: {libro_id: libro_id, indices: indices },
				dataType: "json",
				success: function(data) {
						$('#img-loader').hide();
	                    if(data.ok ==1){
	                    $('#excel-btn').attr('data-href',data.excel);
	                    $('#btn-excel').show();
	                    $('#alert-success').hide();
                    }
                    else{
                    	$('#excelLoader').hide();
						$('#generar-excel').show();
						$('#div-error').html(data.msg);
				        notify($('#div-error').html(data.msg),'danger','<b>'+$('#error-msg-server').val()+'</b><BR>');
                    }
                    
                    
					
				},
				error: function(){
					$('#div-error').html($('#error-msgc-excel').val());
					notify($('#div-error').html());
					$('#btn-process').show();
					$('#formCodigos').safeform('complete');
                    return false;
				}
			});



   })

	
   observe();
   
	

});



function observe(){
	
	 $('#listado .listaC').off('click');
	 $('#listado').on('click', '.listaC', function(){
	 	$('#libro_id').val($(this).attr('data-libro'));
	 	$('#titulo_libro').val($(this).attr('data-name'));
	 	$('#tipo_libro').val($(this).attr('data-tipo'));
	 	$('#grado_libro').val($(this).attr('data-grado'));
	 });

}




function waitSearch()
{
	$('#search').hide();
	$('#listado').hide();
	$('#panelTitle').text($('#encabezado').val());
    $('#div-paginas').show();
    $('.load1').show();
}

function stopWaitSearch()
{
	$('#search').show();
    $('.load1').hide();
}


function reiniciarCampos()
{
	$('#renovable').prop('checked',false);
	$('#cantidad_renovaciones').val(0);
	$('#cantidad_renovaciones').attr('readonly',true);
	$('#cantidad_ejemplares').val('');
}


    

    