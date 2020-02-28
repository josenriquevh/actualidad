$(document).ready(function() 
{
	$('.date_picker').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    startDate: '0d',
	    clearBtn: true
	}); 

	$('.delete').click(function(){
        var noticia_id = $(this).attr('data');
        sweetAlertDelete(noticia_id, 'AdminNoticia');
    });

	$('.uploadFileHref').click(function(){
		$('#file_input').val($(this).attr('data-etiqueta'));
		$('#div-error').hide();
	});

	$('.fileupload').fileupload({
        url: $('#url_upload').val(),
        dataType: 'json',
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf)$/i,
        add: function (e, data) {
            var goUpload = true;
            var uploadFile = data.files[0];
            var file_input = $('#file_input').val();
            if (!(/\.(gif|jpg|jpeg|tiff|png)$/i).test(uploadFile.name) && file_input == 'imagen') {
                $('#div-error ul').html("<li>- Debes seleccionar sólo archivo de imagen</li>");
                goUpload = false;
            }
            if (!(/\.(pdf)$/i).test(uploadFile.name) && file_input == 'pdf') {
                $('#div-error ul').html("<li>- Debes seleccionar sólo archivo PDF</li>");
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
                    var img = $('#foto_img');
                    img.attr("src", uploads+base_upload+file.name);
                }
                $('#'+file_input).val(base_upload+file.name);
            });
        }
    });

    $("#btn_clear").on("click",function(event) {
        $("#imagen").val("");
        $("#figure").html('<img id="foto_img" src="'+$('#photo').val()+'" style="width: 512px; height: auto; margin: 0 1rem;">');
    });

    $("#btn_clear2").on("click",function(event) {
        $("#pdf").val("");
    });
    
});

