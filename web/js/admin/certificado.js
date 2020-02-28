$(document).ready(function() {

    var url_libros = $('#url_libros').val();
    var empresa_id = $('#empresa_id').val();
    var certificado_id = $('#certificado_id').val();
    var editar = $('#tipo_certificado_id').val();

    if(editar => 1 )
    { 
        console.log('aca');
        var tipo_certificado_id = $('#tipo_certificado_id').val();
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        if(tipo_certificado_id == 1 && grado_id != '' && empresa_id != '')
        {
            $.ajax({
                type: "GET",
                url: url_libros,
                dataType: "json",
                data: { tipo_certificado_id: tipo_certificado_id, empresa_id: empresa_id, certificado_id: certificado_id, grado_id: grado_id },
                success: function(data){
    
                    $('.tipo_entidad').html(data.html);
                },
                error: function(){
                    $('#alert-error').html($('#error_msg-edit').val());
                    $('#div-alert').show();
                }
            });
            $('.tipo_entidad').show();

        }else
        {
           $('.tipo_entidad').hide();
        }
        $('.tipo_entidad').show();
    }
	$('#tipo_certificado_id, #grado_id, #empresa_id').change(function()
    {
        var tipo_certificado_id = $('#tipo_certificado_id').val();
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        if(tipo_certificado_id == 1 && grado_id != '' && empresa_id != '')
        {
            $.ajax({
                type: "GET",
                url: url_libros,
                dataType: "json",
                data: { tipo_certificado_id: tipo_certificado_id, empresa_id: empresa_id, certificado_id: certificado_id, grado_id: grado_id },
                success: function(data){
    
                    $('.tipo_entidad').html(data.html);
                },
                error: function(){
                    $('#alert-error').html($('#error_msg-edit').val());
                    $('#div-alert').show();
                }
            });
            $('.tipo_entidad').show();

        }else
        {
           $('.tipo_entidad').hide();
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
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf)$/i,
        add: function (e, data) {
	        var goUpload = true;
	        var uploadFile = data.files[0];
	        var file_input = $('#file_input').val();
	        if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && file_input == 'form_foto') {
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
        		if (file_input == 'form_foto')
        		{
                    console.log('aca');
        			var img = $('#foto_img');
        			img.attr("src", uploads+base_upload+file.name);
                }
                console.log(file_input);
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
    });

    

});
function buscarEntidad(tipo_certificado_id)
{
    var tipo_certificado_id = $('#tipo_certificado_id').val();
            var grado_id = $('#grado_id').val();
            var empresa_id = $('#empresa_id').val();
            if(tipo_certificado_id == 1 && grado_id != '' && empresa_id != '')
            {
                $.ajax({
                    type: "GET",
                    url: url_libros,
                    dataType: "json",
                    data: { tipo_certificado_id: tipo_certificado_id, empresa_id: empresa_id, certificado_id: certificado_id, grado_id: grado_id },
                    success: function(data){
        
                        $('.tipo_entidad').html(data.html);
                    },
                    error: function(){
                        $('#alert-error').html($('#error_msg-edit').val());
                        $('#div-alert').show();
                    }
                });
                $('.tipo_entidad').show();

            }else
            {
            $('.tipo_entidad').hide();
            }
}