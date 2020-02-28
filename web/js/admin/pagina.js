$(document).ready(function() {

	var root_site = $('#root_site').val();

    CKEDITOR.replace( 'form_descripcion', {
    	filebrowserUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/paginas/',
	    filebrowserImageUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/paginas/',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.form_descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.form_descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	CKEDITOR.replace( 'form_contenido', {
		filebrowserUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/paginas/',
	    filebrowserImageUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/paginas/',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.form_contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.form_contenido.getData();
				var elem = document.getElementById("deslen2");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	$('.nextBtn, .stepwizard-step').click(function(){

		// Cantidad de caracteres en la descripción
		var editor_descripcion = CKEDITOR.instances.form_descripcion.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_descripcion.replace(/<[^>]+>/g, '').length);

		// Cantidad de caracteres en el contenido
		var editor_contenido = CKEDITOR.instances.form_contenido.getData();
		var deslen2 = document.getElementById("deslen2");
		deslen2.value = parseInt(editor_contenido.replace(/<[^>]+>/g, '').length);

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
	        if (!(/\.(pdf)$/i).test(uploadFile.name) && file_input == 'form_pdf') {
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
        		if (file_input == 'form_foto')
        		{
        			var img = $('#foto_img');
        			img.attr("src", uploads+base_upload+file.name);
        		}
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
    });

	$("#btn_clear").on("click",function(event) {
        $("#form_foto").val("");
        $("#figure").html('<img id="foto_img" src="'+$('#photo').val()+'" style="width: 512px; height: auto; margin: 0 1rem;">');
    });

    $("#btn_clear2").on("click",function(event) {
        $("#form_pdf").val("");
    });

    $('#form_empresa, #form_tipoPagina').change(function(){
        var pagina_id = $('#pagina_id').val();
        var empresa_id = $('#form_empresa').val();
        var tipo_pagina_id = $('#form_tipoPagina').val();
        if (empresa_id != '' && tipo_pagina_id == 2)
        {
            $('#div-referencia').hide();
            $.ajax({
                type: "GET",
                url: $("#url_referencia").val(),
                async: true,
                data: { empresa_id: empresa_id, pagina_id: pagina_id },
                dataType: "json",
                success: function(data) {
                    $('#div-referencia').html(data.html);
                    $('#div-referencia').show();
                },
                error: function(){
                    $('#div-error-server').html($('#error-msg-referencia').val());
                    notify($('#div-error-server').html());
                }
            });
        }
        else {
            $('#div-referencia').html('');
            $('#div-referencia').show();
        }
	});
	

});
