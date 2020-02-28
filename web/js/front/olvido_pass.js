$(document).ready(function() {

    $('#button-validar').click(function(){
		var usuario = $('#usuario').val();
        var codLibro = $('#codLibro').val();

        $.ajax({
            type: "GET",
            url: $("#url_validar").val(),
            async: true,
            data: { login: usuario, codLibro: codLibro },
            dataType: "json",
            success: function(data) {
                if(data.ok == 1){
                    if(data.validar == 1){
                        $('#form-olvidoPass').hide();
                        $('#button-validar').hide();
                        $('#text1').hide();
                        $('#div-step2').show();
                        $('#button-2').show();
                        $('#text2').show();
                        $('#usuario_id').val(data.usuario_id);
                    }else{
                        $('#text_error').show();
                    }
                }
            },
            error: function(){
                
            }
        });
    });
    
    $('#button-recuperar').click(function(){
		var contrasena1 = $('#passLoginA').val();
        var usuario_id = $('#usuario_id').val();

        $.ajax({
            type: "GET",
            url: $("#url_contrasena").val(),
            async: true,
            data: { contrasena1: contrasena1, usuario_id: usuario_id },
            dataType: "json",
            success: function(data) {
                if(data.ok == 1){
                    if(data.exito == 1){
                        $('#modalexito').modal('show');
                    }else{
                        $('#modalerror').modal('show');
                    }
                }else{
                    $('#modalerror').modal('show');
                }
            },
            error: function(){
                
            }
        });
	});

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

    $("#codLibro").keypress(function(e){

        var keyCode = e.which;
        /* 
        48-57 - (0-9)Numbers
        65-90 - (A-Z)
        97-122 - (a-z)
        8 - (backspace)
        32 - (space)
        
        // Not allow special */
        if ( !( (keyCode >= 48 && keyCode <= 57) 
            ||(keyCode >= 65 && keyCode <= 90) 
            || (keyCode >= 97 && keyCode <= 122) ) 
            && keyCode != 8) {
            e.preventDefault();
        }

        var code = $(this).val();
        if (code.length < 2)
        {
            if ( !( (keyCode >= 65 && keyCode <= 90) 
                || (keyCode >= 97 && keyCode <= 122) ) ) {
                e.preventDefault();
            }
        } 
        else if ((code.length == 2 || code.length == 4) && keyCode != 8)
        {
            if (code.length == 2)
            {
                if (keyCode >= 48 && keyCode <= 57)
                {
                    $(this).val(code+'-');
                }
                else {
                    e.preventDefault();
                }
            }
            else if (code.length == 4) {
                $(this).val(code+'-');
            }
            else {
                e.preventDefault();
            }
        }
        
    });
    $('#usuario, #codLibro').focusout(function(){
        var valid = $("#form-olvidoPass").valid();
        if (!valid) 
        {
            $("#button-validar").addClass( "blocked" );
        }
        else {
            $("#button-validar").removeClass( "blocked" );
        }
    });    

    $("#codLibro").keyup(function(){
        
        var valid = $("#form-olvidoPass").valid();
        if (!valid) 
        {
            $("#button-validar").addClass( "blocked" );
        }
        else {
            $("#button-validar").removeClass( "blocked" );
        }
       
    });

    /*$("#form-olvidoPass").validate({
        ignore: "",
        errorPlacement: function(error, element) {},
        rules: {
            'usuario': {
                required: true,
                minlength: 6,
                maxlength: 20
            },
            'codLibro': {
                required: true,
                minlength: 10
            }
        }
    });*/

    $('#passLoginA, #passLoginB').focusout(function(){
        var valid = $("#form-pass").valid();
        if (!valid) 
        {
            $("#button-recuperar").addClass( "blocked" );
        }
        else {
            $("#button-recuperar").removeClass( "blocked" );
        }
    });

    $("#passLoginB").keyup(function(){
        var passLoginA = $('#passLoginA').val();
        var passLoginB = $('#passLoginB').val();
        if (passLoginA == passLoginB){
            var valid = $("#form-pass").valid();
            if (!valid) 
            {
                $("#button-pasrecuperars").addClass( "blocked" );
            }
            else {
                $("#button-recuperar").removeClass( "blocked" );
            }
        }
        else {
            $("#button-recuperar").addClass( "blocked" );
        }
    }); 

    /*$("#form-pass").validate({
        ignore: "",
        errorPlacement: function(error, element) {},
        rules: {
            'passLoginA': {
                required: true,
                minlength: 6,
                maxlength: 8,
                alphanumeric: true
            },
            'passLoginB': {
                equalTo: "#passLoginA"
            }
        }
    });*/

    $('#continuar').click(function(){
        
        window.location=($('#url_continuar').val());
    });

    $('#continuar-error').click(function(){
        
        $('#modalerror').modal('hide');
    });

    $('#button-recuperar').click(function(){
        
        $("#button-recuperar").addClass( "blocked" );
    });
    
});