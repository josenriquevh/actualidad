$(document).ready(function() {
    
    $('.btn-newCode').click(function(){
        var button = $(this);
        var libro_id = button.attr('data');
        var codigo = $('#codigo-'+libro_id).val();
        button.hide();
        $('#error-code-'+libro_id).hide();
        $('#loader-code-'+libro_id).show();
        $.ajax({
           type:"POST",
           url: $('#url_codigo').val(),
           async: true,
           data: { pagina_id: libro_id, codigo: codigo },
           dataType: "json",
           success: function(data){
                if (data.ok == 1)
                {
                    $("#card-libro-"+libro_id).removeClass( "inactivo" );
                    $("#card-libro-"+libro_id).attr("href", $('#url_unidades').val()+'/'+libro_id);
                    $('#agregar-cod-'+libro_id).hide();
                    $('#add-code-'+libro_id).hide();
                }
                else {
                    button.show();
                    $('#error-code-'+libro_id).html(data.msg);
                    $('#error-code-'+libro_id).show();
                    $('#loader-code-'+libro_id).hide();
                }
           },
           error: function(){
                button.show();
                $('#error-code-'+libro_id).html($('#error_code_msg').val());
                $('#error-code-'+libro_id).show();
                $('#loader-code-'+libro_id).hide();
           }
        });
    });

    $('#filter_grado').change(function(){
        var filter_grado = $('#filter_grado').val();
        if (filter_grado == '')
        {
            $('.grado').show();
        }
        else {
            $('.grado').hide();
            $('#grado-'+filter_grado).show();
        }
    });

    $(".input-code").keypress(function(e){
        
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

    });
    
});
