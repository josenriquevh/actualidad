$(document).ready(function() {

	$('#guardar').click(function(){
		$('#form-actualizacion').submit();
	});

	$('#passSwitch').click(function(){
		if($(this).is(":checked")){
			$("#guardar").addClass( "blocked" );
			$('#correo, #nombre, #apellido').focusout(function(){
				var valid = $("#form-actualizacion").valid();
				if (!valid) 
				{
					$("#guardar").addClass( "blocked" );
				}
				else {
					$("#guardar").removeClass( "blocked" );
				}
			});

			$("#passLoginB").keyup(function(){
				var passLoginA = $('#passLoginA').val();
				var passLoginB = $('#passLoginB').val();
				if (passLoginA == passLoginB){
					var valid = $("#form-actualizacion").valid();
					if (!valid) 
					{
						$("#guardar").addClass( "blocked" );
					}
					else {
						$("#guardar").removeClass( "blocked" );
					}
				}
				else {
					$("#guardar").addClass( "blocked" );
				}
			});
		}
		else if($(this).is(":not(:checked)")){

			$('#passLoginA').val("");
			$('#passLoginB').val("");

			var valid = $("#form-actualizacion").valid();
			if (!valid) 
			{
				$("#guardar").addClass( "blocked" );
			}
			else {
				$("#guardar").removeClass( "blocked" );
			}

			$('#correo, #nombre, #apellido').focusout(function(){
				var valid = $("#form-actualizacion").valid();
				if (!valid) 
				{
					$("#guardar").addClass( "blocked" );
				}
				else {
					$("#guardar").removeClass( "blocked" );
				}
			});
		}
	});

	$('#correo, #nombre, #apellido').focusout(function(){
		var valid = $("#form-actualizacion").valid();
		if (!valid) 
		{
			$("#guardar").addClass( "blocked" );
		}
		else {
			$("#guardar").removeClass( "blocked" );
		}
	});

	/*$("#form-actualizacion").validate({
		ignore: "",
		errorPlacement: function(error, element) {},
		rules: {
			'nombre': {
				required: true,
				minlength: 3
			},
			'apellido': {
				required: true,
				minlength: 3
			},
			'passLoginA': {
                required: {
                    depends: function(element) {
                        return $("#passSwitch").is(":checked");
                        }
                    },
				minlength: 6,
				maxlength: 8,
				alphanumeric: true
			},
			'passLoginB': {
				equalTo: "#passLoginA"
			},
			'correo': {
				email: true,
				required: true
			}
		}
	});*/

	// Mostrar o no la contraseña
	$('#imgPassA').click(function(){
		var passLogin_input = $("#passLoginA");
		if (passLogin_input.attr('type') == 'password')
		{
			passLogin_input.attr('type', 'text');
			$('#imgPassA').attr('src', $('#eye_blocked').val());
		}
		else {
			passLogin_input.attr('type', 'password');
			$('#imgPassA').attr('src', $('#eye_unblocked').val());
		}
	});
	$('#imgPassB').click(function(){
		var passLogin_input = $("#passLoginB");
		if (passLogin_input.attr('type') == 'password')
		{
			passLogin_input.attr('type', 'text');
			$('#imgPassB').attr('src', $('#eye_blocked').val());
		}
		else {
			passLogin_input.attr('type', 'password');
			$('#imgPassB').attr('src', $('#eye_unblocked').val());
		}
	});
	
	//observe();

	
	
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
                    $("#figure").html('<img id="foto_img" src="'+uploads+base_upload+file.name+'" class="img-fluid bg">');
        			//img.attr("src", uploads+base_upload+file.name);
        		}
        		$('#'+file_input).val(base_upload+file.name);
            });
        }
	});
	
	$('#provincia, #ciudad, #colegio').change(function(){
        var id = $(this).val();
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var reference = $(this).attr('reference');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        if (id != '')
        {
            if (field_update == 'seccion')
            {
                $('#'+field_update).hide();
                $('#loader-'+field_update).show();
                $('.color-error').hide();
                $.ajax({
                    type: "GET",
                    url: $("#url_select_seccion").val(),
                    async: true,
                    data: { colegio_id: id, entity: entity, grado_id: $('#grado_id').val(), orderBy: orderBy },
                    dataType: "json",
                    success: function(data) {
                        $('#'+field_update).html(data.options);
                        $('#'+field_update).show();
                        $('#loader-'+field_update).hide();
                        $('#selectdiv-'+field_update+', #'+field_update).removeClass('input-disabled');
                    },
                    error: function(){
                        $('#'+field_update+'-error').html($('#error-msg-'+field_update).val());
                        $('.color-error').show();
                    }
                });
            }
            else {
                selectDependiente(id, entity, field_update, reference, orderBy);
            }
        }
	});
	
	$('#grado_id').change(function(){
        var colegio_id = $('#colegio').val();
		var grado_id = $('#grado_id').val();
		if (grado_id != '' && colegio_id != '')
		{
			$('#seccion').hide();
			$('#loader-seccion').show();
		    $.ajax({
				type: "GET",
				url: $('#url_seccion').val(),
				async: true,
				data: { colegio_id: colegio_id, grado_id: grado_id},
				dataType: "json",
				success: function(data) {
					console.log(data);
                    if(data.ok == 1)
                    {
						$('#seccion').html(data.options);
						$('#loader-seccion').hide();
                        $('#seccion').show();
                    }
                    else
                    {
                        alert(data.msg);
                    }
				},
				error: function(){
					$('#div-error-server').html($('#error-msg-seccion_id').val());
		            notify($('#div-error-server').html());
				}
			});
		}
	});
	
});
