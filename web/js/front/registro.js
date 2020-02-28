$(document).ready(function() {

    $('#btn-newCode').click(function(){
        var grado_id = $('#grado_id').val();
        var codigo = $('#codLibro').val();
        var tipo_pagina_id = $('#tipo_pagina_id').val();
        var pu_ids = $('#pu_ids').val();
        $('#btn-newCode').addClass('blocked');
        $('#codLibro-error').hide();
        $('#loader-code').show();
        $.ajax({
           type:"POST",
           url: $('#url_codigo').val(),
           async: true,
           data: { grado_id: grado_id, codigo: codigo, tipo_pagina_id: tipo_pagina_id, pu_ids: pu_ids },
           dataType: "json",
           success: function(data){
                $('#loader-code').hide();
                if (data.ok == 1)
                {    
                    $("#div-codes").append(data.card);
                    $('#grado_id').val(data.grado_id);
                    $('#grado').val(data.grado_id);
                    $('#pu_ids').val(data.pu_ids);
                    $('#codLibro').val('');
                    $('#button-step1').removeClass('blocked');
                }
                else {
                    $("#btn-newCode").removeClass( "blocked" );
                    $('#codLibro-error').html(data.error);
                    $('#codLibro-error').show();
                }
           },
           error: function(){
                $("#btn-newCode").removeClass( "blocked" );
                $('#loader-code').hide();
                $('#codLibro-error').html($('#errorMsg-code').val());
                $('#codLibro-error').show();
           }
        });
    });

    $("#codLibro").keypress(function(e){
        
        var keyCode = e.which;
        /* 
        48-57 - (0-9)Numbers
        65-90 - (A-Z)
        97-122 - (a-z)
        8 - (backspace)
        32 - (space)
        */
        // Not allow special 
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
        else if (code.length == 3 && keyCode != 8 && !(keyCode >= 48 && keyCode <= 57))
        {
            e.preventDefault();
        }

        if (code.length >= 9)
        {
            $("#btn-newCode").removeClass( "blocked" );
        }
        else {
            $('#btn-newCode').addClass('blocked');
        }

    });

    $("#codLibro").bind("paste", function(e){
        var code = e.originalEvent.clipboardData.getData('text');
        if (code.length >= 10)
        {
            $("#btn-newCode").removeClass( "blocked" );
        }
        else {
            $('#btn-newCode').addClass('blocked');
        }
    } );

    $('#button-step1').click(function(){

        // Esconder el primer paso
        var options = {};
        $('.teacher').hide("drop", options, 1000);
        $( "#div-step1" ).hide("drop", options, 1000);

        // Mostrar el segundo paso
        setTimeout(function() {
            $('.teacher').show("fade", options, 500);
            $( "#div-step2" ).show("fold", options, 1000);
            $('#step1').addClass('completo');
            $('#span-step2').addClass('completo');
            $('label.error').hide();
            $('#usernameCheckedError').hide();
            $('#username-wrong').hide();
        }, 2000);

    });

    $('#usuario').focusout(function(){
        var usuario = $.trim($('#usuario').val());
        if (usuario != '' && usuario.length > 5 && usuario.length < 21 && /^[\w.]+$/i.test(usuario))
        {
            $.ajax({
               type:"POST",
               url: $('#url_username').val(),
               async: true,
               data: { login: usuario },
               dataType: "json",
               success: function(data){
                    if (data.ok == 0)
                    {    
                        $('#usernameChecked').val(1);
                        $('#username-checked').show();
                        var valid = $("#form-step2").valid();
                        if (!valid) 
                        {
                            $("#button-step2").addClass( "blocked" );
                        }
                        else {
                            var correcto = 1;
                            console.log(correcto);
                            if(correcto == 1)
                            {
                                $("#button-step2").removeClass( "blocked" );
                            }else{
                                $("#button-step2").addClass( "blocked" );
                            }
                        }
                    }
                    else {
                        $('#usernameChecked').val(0);
                        $('#username-checked').hide();
                        $('#username-wrong').show();
                        $('#usernameCheckedError').show();
                        $("#button-step2").addClass( "blocked" );
                    }
               },
               error: function(){
                    $("#button-step2").addClass( "blocked" );
                    $('#username-error').html($('#errorMsg-username').val());
                    $('#username-error').show();
               }
            });
        }
        else {
            console.log('aca2');
            $("#button-step2").addClass( "blocked" );
            $('#usernameChecked').val(0);
            $('#username-checked').hide();
        }
    });

    $('#usuario').focusin(function(){
        $('#username-error').hide();
        $('#username-wrong').hide();
        $('#usernameCheckedError').hide();
    });

    $('#email, #passLoginA, #passLoginB').focusout(function(){
        var valid = $("#form-step2").valid();
        if (!valid) 
        {
            $("#button-step2").addClass( "blocked" );
        }
        else {
            var correcto = $('#usernameChecked').val();
            if(correcto == 1)
            {
                $("#button-step2").removeClass( "blocked" );
            }else{
                $("#button-step2").addClass( "blocked" );
            }
        }
    });

    $("#passLoginB").keyup(function(){
        var passLoginA = $('#passLoginA').val();
        var passLoginB = $('#passLoginB').val();
        if (passLoginA == passLoginB){
            var valid = $("#form-step2").valid();
            if (!valid) 
            {
                $("#button-step2").addClass( "blocked" );
            }
            else {
                var correcto = $('#usernameChecked').val();
                
                if(correcto == 1)
                {
                    $("#button-step2").removeClass( "blocked" );
                }else{
                    $("#button-step2").addClass( "blocked" );
                }
            }
        }
        else {
            $("#button-step2").addClass( "blocked" );
        }
    });

    // Mostrar o no la contraseña
    $('.eye').click(function(){
        var img = $(this);
        var passLogin_name = $(this).attr('data-tipo');
        var passLogin_input = $("#"+passLogin_name);
        if (passLogin_input.attr('type') == 'password')
        {
            passLogin_input.attr('type', 'text');
            img.attr('src', $('#eye_blocked').val());
        }
        else {
            passLogin_input.attr('type', 'password');
            img.attr('src', $('#eye_unblocked').val());
        }
    });

    $('#button-step2').click(function(){

        // Pasar los datos ingresados a los campos hidden
        $('#login').val($('#usuario').val());
        $('#correo').val($('#email').val());
        $('#clave').val($('#passLoginA').val());

        // Esconder el segundo paso
        var options = {};
        $('.teacher').hide("drop", options, 1000);
        $( "#div-step2" ).hide("drop", options, 1000);

        // Mostrar el tercer paso
        setTimeout(function() {
            $('.teacher').show("fade", options, 500);
            $( "#div-step3" ).show("fold", options, 1000);
            $('#step2').addClass('completo');
            $('#span-step3').addClass('completo');
        }, 2000);

    });

    $('#first_name, #last_name').focusout(function(){
        var first_name = $.trim($('#first_name').val());
        var last_name = $.trim($('#last_name').val());
        $('#first_name').val(first_name);
        $('#last_name').val(last_name);
        var valid = $("#form-step3").valid();
        if (!valid) 
        {
            $("#button-step3").addClass( "blocked" );
        }
        else {
            $("#button-step3").removeClass( "blocked" );
        }
    });

    $("#form-step3").validate({
        ignore: "",
        errorPlacement: function(error, element) {},
        rules: {
            'first_name': {
                required: true,
                minlength: 2,
                maxlength: 50
            },
            'last_name': {
                required: true,
                minlength: 2,
                maxlength: 50
            }
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

    $('#button-step3').click(function(){

        // Pasar los datos ingresados a los campos hidden
        $('#nombre').val($('#first_name').val());
        $('#apellido').val($('#last_name').val());
        $('#provincia_id').val($('#provincia').val());
        $('#ciudad_id').val($('#ciudad').val());
        $('#colegio_id').val($('#colegio').val());
        $('#seccion_id').val($('#seccion').val());

        // Esconder el tercer paso
        var options = {};
        $('.teacher').removeClass('d-flex');
        $('.teacher').removeClass('flex-column');
        $('.teacher').hide();
        $( "#div-step3" ).hide("drop", options, 1000);

        // Mostrar el loader y hacer el submit
        setTimeout(function() {
            $('#loader').show();
            $('#form-send').submit();
        }, 2000);

    });

    

    /*$('#usuario, #email, #passLoginA').focusin(function() {
        var name = $(this).attr('name');
        console.log(name);
        var namefull = $('#inner-popover-'+name);
        if(name == 'usuario')
        {
            $(namefull).html(' ');
            $(namefull).html(name);
            console.log(namefull);
            $(namefull).addClass('popover-body');
            $(namefull).removeClass('popover-body-error');
        }
        else if(name == 'email')
        {
            $(namefull).html(' ');
            $(namefull).html(name);
            console.log(namefull);
            $(namefull).addClass('popover-body');
            $(namefull).removeClass('popover-body-error');
        }
        else if(name == 'passLoginB')
        {
            $(namefull).removeClass('popover-body');
        }
       /* $('.popover-body-error').addClass('popover-body');
        $('.popover-body').removeClass('popover-body-error');
    });*/
    
});
