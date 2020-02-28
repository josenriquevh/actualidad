$(document).ready(function() {

    validar_tipo_elemento($('#form_tipoElemento').val());
    
    $('#form_tipoElemento').change(function()
    {
        validar_tipo_elemento($(this).val());
    });

    $('#form_tipoPregunta').change(function()
    {
        $('#tipo_pregunta_id').val(0);
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
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && file_input == 'imagen') {
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
        		{
                    $("#figure").html('<img id="foto_img" src="'+uploads+base_upload+file.name+'" alt="" class="img_figure">');
        		}
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
    });

	$("#btn_clear").on("click",function(event) {
        $('#imagen').val('NULL');
        $("#figure").html('<img id="foto_img" src="'+$('#avatar').val()+'" alt="" class="img_figure">');
    });

});

function validar_tipo_elemento(tipo_elemento)
{

    $('#tipo_pregunta_id').val(tipo_elemento);
   
    //validamos que si el tipo de elemento es interactivo, el tipo de pregunta por defecto debe ser simple
    if(tipo_elemento == $('#elemento_interactivo').val() )
    {
        $('#tipo_pregunta_id').val($('#pregunta_simple').val());
        $('#form_tipoPregunta').val($('#pregunta_simple').val());
        $('#form_tipoPregunta').attr("disabled", true);
    }else
    {
        $('#tipo_pregunta_id').val(0);
        $('#form_tipoPregunta').attr("disabled", false);
    }

}