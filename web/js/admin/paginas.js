$(document).ready(function() {

    $('#grado_id, #empresa_id').change(function(){
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        $('#div-paginas').show();
        $('#listado').hide();
        if (grado_id != 0 && empresa_id != 0)
        {
            $('.load1').show();
            $.ajax({
                type: "GET",
                url: $("#url_paginas").val(),
                async: true,
                data: { grado_id: grado_id, empresa_id: empresa_id },
                dataType: "json",
                success: function(data) {
                    $('.load1').hide();
                    $('#listado').html(data.html);
                    $('#listado').show();
                    applyDataTableReorder();
                    observe();
                },
                error: function(){
                    $('#div-error-server').html($('#error-msg-paginas').val());
                    notify($('#div-error-server').html());
                }
            });
        }
    });

    applyDataTableReorder();

    $('#guardar').click(function(){
        $('#form').submit();
        return false;
    });

    $('#form').submit(function(e) {
        e.preventDefault();
    });

    $('#form').safeform({
        submit: function(e) {
            
            $('#div-alert').hide();
            if ($("#form").valid())
            {
                $('#guardar').prop('disabled', true);
                $.ajax({
                    type: "POST",
                    url: $('#form').attr('action'),
                    async: true,
                    data: $("#form").serialize(),
                    dataType: "json",
                    success: function(data) {
                        $('.form-control').val('');
                        $('#inserts').html(data.inserts);
                        treePaginas(data.id);
                        initModalShow();

                        // manual complete, reenable form ASAP
                        $('#form').safeform('complete');
                        return false; // revent real submit

                    },
                    error: function(){
                        $('#alert-error').html($('#error_msg-save').val());
                        $('#div-alert').show();
                        $('#guardar').prop('disabled', false);
                        $('#form').safeform('complete');
                        return false; // revent real submit
                    }
                });
            }
            else {
                $('#form').safeform('complete');
                return false; // revent real submit
            }
            
        }
    });

    $('#aceptar').click(function(){
        window.location.replace($('#url_list').val());
    });

    $('.paginate_button').click(function(){
        afterPaginate();
    });

    observe();

    disableSubmit();

});

function afterPaginate(){
    observe();
}

function observe()
{

    $('.tree').jstree();

    $('.delete').unbind('click');
    $('.delete').click(function(){
        var pagina_id = $(this).attr('data');
        sweetAlertDelete(pagina_id, 'EaPagina', $('#url_delete_pagina').val());
    });

    $('.duplicate').unbind('click');
    $('.duplicate').click(function(){
        var pagina_id = $(this).attr('data');
        $('#pagina_id').val(pagina_id);
        initModalEdit();
        $.ajax({
           type:"GET",
           url: $('#url_edit').val(),
           async: true,
           data: { pagina_id: pagina_id },
           dataType: "json",
           success: function(data){
                enableSubmit();
                $('#titulo').val(data.titulo);
                $('#tipo_pagina_id').html(data.tipos_str);
                $('#estatus_contenido_id').html(data.status_str);
           },
           error: function(){
                $('#alert-error').html($('#error_msg-edit').val());
                $('#div-alert').show();
           }
        });
    });

}

function treePaginas(pagina_id)
{
    $('#tree_paginas').jstree({
        'core' : {
            'data' : {
                "url" : $('#url_tree').val()+'/'+pagina_id,
                "dataType" : "json"
            }
        }
    });
}