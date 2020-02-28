$(document).ready(function() {

    $('#fecha_nacimiento').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es'
	});

	$('#cambiar').click(function(){
		if ($(this).is(':checked'))
		{
			$('#clave').prop('disabled', false); // Se activa el campo de contraseña
		}
		else {
			$('#clave').prop('disabled', true); // Se desactiva el campo de contraseña
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
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && file_input == 'foto') {
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
        		if (file_input == 'foto')
        		{
        			var img = $('#foto_img');
                    $("#figure").html('<img id="foto_img" src="'+uploads+base_upload+file.name+'" class="img_figure">');
        			//img.attr("src", uploads+base_upload+file.name);
        		}
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
    });

	$("#btn_clear").on("click",function(event) {
        $("#foto").val("");
        $("#figure").html('<img id="foto_img" src="'+$('#avatar').val()+'" class="img_figure">');
    });

    $('.delete').unbind('click');
    $('.delete').click(function(){
        var pagina_id = $(this).attr('data');
        sweetAlertDelete(usuario_id, 'AdminUsuario');
    });

});
