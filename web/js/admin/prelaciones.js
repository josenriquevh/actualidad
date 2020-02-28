$(document).ready(function() {

    $('#empresa_id').change(function(){
        var empresa_id = $('#empresa_id').val();
        var padre_pagina_id = '';
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        $( "#empresa_id" ).blur();
        if (empresa_id != '')
        {
            selectDependientePagina(empresa_id, padre_pagina_id, entity, field_update, orderBy, 0);
        }
    });

    $('#pagina_padre_id').change(function(){
        var pagina_padre_id = $('#pagina_padre_id').val();
        $('#div-paginas').show();
        $('#listado').hide();
        $('.load1').show();
        $( "#pagina_padre_id" ).blur();
        $.ajax({
            type: "GET",
            url: $("#url_unidades").val(),
            async: true,
            data: { pagina_padre_id: pagina_padre_id },
            dataType: "json",
            success: function(data) {
                $('.load1').hide();
                $('#listado').html(data.html);
                $('#listado').show();
                applyDataTable();
                observe();
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-pagina_id').val());
                notify($('#div-error-server').html());
            }
        });
    });

    $('.paginate_button').click(function(){
        afterPaginate();
    });

});

function afterPaginate(){
    observe();
}

function observe()
{

    $('.tree').jstree();

    $('.prelacion').click(function(){
        var pagina_id = $(this).attr('data');
        $('#a-'+pagina_id).hide();
        $('#loader-'+pagina_id).show();
        $.ajax({
            type: "GET",
            url: $("#url_hermanas").val(),
            async: true,
            data: { pagina_id: pagina_id },
            dataType: "json",
            success: function(data) {
                $('#loader-'+pagina_id).hide();
                $('#select-'+pagina_id).html(data.html);
                $('.select-'+pagina_id).show();
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-pagina_id').val());
                notify($('#div-error-server').html());
            }
        });
    });

    $('.chk').click(function(){
        var pagina_id = $(this).attr('data');
        var prelada = $('#select-'+pagina_id).val();
        $('.select-'+pagina_id).hide();
        $('#loader-'+pagina_id).show();
        $.ajax({
            type: "POST",
            url: $("#url_prelacion").val(),
            async: true,
            data: { pagina_id: pagina_id, prelada: prelada },
            dataType: "json",
            success: function(data) {
                $('#td-titulo-'+pagina_id).html(data.html);
                $('#loader-'+pagina_id).hide();
                $('.select-'+pagina_id).hide();
                $('#a-'+pagina_id).show();
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-prelada').val());
                notify($('#div-error-server').html());
            }
        });
    });

}
