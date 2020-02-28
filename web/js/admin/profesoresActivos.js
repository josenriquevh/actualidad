$(document).ready(function() {
	
	$('#excel-btn').click(function(){
		$(this).hide();
		$('#excelLoader').show();
		$.ajax({
			type: "POST",
			url: $('#url-generar-excel').val(),
			async: true,
			dataType: "json",
			success: function(data) {
				if(data.ok == 1){
					$('#excelLoader').hide();
					$('#descargarExcel').attr('data-href',data.archivo);
					$('#descargarExcel').show();
				}
				else{
					$('#excelLoader').hide();
					$('#excel-btn').show();
					$('#div-error').html($('#error-msg-excel').val());
			        notify($('#div-error').html(),'danger','<b>'+$('#error-msg-server').val()+'</b><BR>');
				}
			},
			error: function(){
				$('#div-error').html($('#error-msg-excel').val());
			    notify($('#div-error').html(),'danger','<b>'+$('#error-msg-server').val()+'</b><BR>');
				$('#excelLoader').hide();
				$('#excel-btn').show();
			}
		});
	});

	$('#descargarExcel').click(function(){
		window.location.href = $(this).attr('data-href');
	});

	observe();

	$('.paginate_button').click(function(){
        observe();
    });
	
});

function observe()
{
	$('.tree').jstree();
}