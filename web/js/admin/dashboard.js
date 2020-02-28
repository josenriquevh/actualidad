$(document).ready(function() {

    $('#provincia_id').change(function(){
        var provincia_id = $('#provincia_id').val();
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var reference = $(this).attr('reference');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        if (provincia_id != '')
        {
            selectDependiente(provincia_id, entity, field_update, reference, orderBy);
            listadoColegios(provincia_id, 0);
        }
    });

    $('#ciudad_id').change(function(){
        var provincia_id = $('#provincia_id').val();
        var ciudad_id = $('#ciudad_id').val();
        if (provincia_id != '' && ciudad_id != '')
        {
            listadoColegios(provincia_id, ciudad_id);
        }
    });

    $('#grado_id').change(function(){
        var grado_id = $('#grado_id').val();
        if (grado_id != '')
        {
            $('#div-libros').hide();
            $('#load_libros').show();
            $.ajax({
                type: "GET",
                url: $("#url_libros").val(),
                async: true,
                data: { grado_id: grado_id },
                dataType: "json",
                success: function(data) {
                    $('#load_libros').hide();
                    $('#div-libros').html(data.html);
                    $('#div-libros').show();
                },
                error: function(){
                    $('#div-error-server').html($('#error-msg-libros').val());
                    notify($('#div-error-server').html());
                }
            });
        }
    });
    
    /*circular progress sidebar home page */   
    $('.progress_profile').circleProgress({ 
        fill: {gradient: ["#2ec7cb", "#6c8bef"]},
        lineCap: 'butt'
    });

    $(window).on('load',function(){
        setTimeout(function(){
            var myvalues = [10,8,5,7,4,2,8,10,8,5,6,4,1,7,4,5,8,10,8,5,6,4,4];
            $('.dynamicsparkline').sparkline(myvalues,{ type: 'bar', width: '100px', height: '20', barColor: '#ffffff', barWidth:'2', barSpacing: 2});
        }, 600);
    });
    
});

function listadoColegios(provincia_id, ciudad_id)
{
    $('#div-colegios').hide();
    $('#load_colegios').show();
    $.ajax({
        type: "GET",
        url: $("#url_colegios").val(),
        async: true,
        data: { provincia_id: provincia_id, ciudad_id: ciudad_id },
        dataType: "json",
        success: function(data) {
            $('#load_colegios').hide();
            $('#div-colegios').html(data.html);
            $('#div-colegios').show();
            applyDataTable();
        },
        error: function(){
            $('#div-error-server').html($('#error-msg-colegios').val());
            notify($('#div-error-server').html());
        }
    });
}