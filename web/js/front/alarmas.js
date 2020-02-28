$(document).ready(function() {

    getAlarma();
    getNotificaciones();
    getDelete();

    $('.page-link').click(function(){
        var pagina_actual = $('#pagina_actual').val();
        var pagina_nueva = $(this).attr('id');
        var offset = $(this).attr('data');

        $('#'+pagina_nueva).addClass('active');
        $('#'+pagina_actual).removeClass('active');
        $('#pagina_actual').val(pagina_nueva);
        
        $('#notificaciones_paginas').hide();
        $('#loader').show();

        $.ajax({
            type: "GET",
            url: $('#url_paginador').val(),
            async: true,
            data: {offset: offset},
            dataType: "json",
            success: function(data) {
                if(data.ok == 1)
                {
                    $('#notificaciones_paginas').html(data.html);     
                    $('#loader').hide();
                    getAlarma();
                    $('#notificaciones_paginas').show();
                    getDelete();
                    
                }
            },
            error: function(){
               
            }
        });
        
    });
    

});

var timer;

function getAlarma()
{
    $.ajax({
        type: "GET",
        url: $('#url_alarma').val(),
        async: true,
        dataType: "json",
        success: function(data) {
            $('#dropNoti').html(data.html);            
            if (data.sonar) {
                $('#sonar').html(data.sonar);
                $('#sonar').show();
         
            }
            else{
                $('#sonar').hide();
                
            }
        },
        error: function(){
           
        }
    });
}

function getNotificaciones()
{
    timer = setInterval(function(){
        getAlarma();
    }, 3600000);
   
}

function getDelete()
{
    $('.mb-1').click(function(){
        if($('.mb-1').is(":checked"))
            {
                console.log('aqui show');
                $('#delete').show();
                $('#delete_notificacion').click(function(){
                $('#form-delete').submit();
                });
            }
        else if ($('.mb-1').is(":not(:checked)"))
        {
            $('#delete').hide();
            console.log('aqui hide');
        }
        });
   
}