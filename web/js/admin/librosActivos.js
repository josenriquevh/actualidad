$(document).ready(function() {

    $('#search').click(function(){
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        $('#listado').hide();
        $('.load1').show();
        $('#descargar').hide();
        $('#generar_excel').show();
        $.ajax({
            type: "GET",
            url: $("#url_paginas").val(),
            async: true,
            data: { grado_id: grado_id, empresa_id: empresa_id },
            dataType: "json",
            success: function(data) {
                if (data.ok == 1)
                {
                    $('.load1').hide();
                    $('#listado').html(data.resultado.html);
                    $('#div-paginas').show();
                    $('#listado').show();
                    applyDataTableReorder();
                }
                else {
                    $('#div-error-server').html(data.msg);
                    notify($('#div-error-server').html());
                }
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-paginas').val());
                notify($('#div-error-server').html());
            }
        });
    });

    $('#generar_excel').click(function(){
        $('#generar_excel').hide();
        $('#excel_loader').show();
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        $.ajax({
            type: "GET",
            url: $("#url_excel").val(),
            async: true,
            data: { grado_id: grado_id, empresa_id: empresa_id },
            dataType: "json",
            success: function(data) {
                if (data.ok == 1)
                {
                    $('#descargar').attr('href', data.resultado.html);
                    $('#excel_loader').hide();
                    $('#descargar').show();
                }
                else {
                    $('#generar_excel').show();
                    $('#excel_loader').hide();
                    $('#div-error-server').html(data.msg);
                    notify($('#div-error-server').html());
                }
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-paginas').val());
                notify($('#div-error-server').html());
            }
        });
    });

});