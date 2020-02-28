$(document).ready(function() {

    $('#continuar').click(function(){

        // Redireccionamiento a procesar la evaluación
        window.location.replace($('#url_procesar').val());

    });

    $('#next').attr("style", "display: none !important");
    
});

function refreshMenu()
{
    $.ajax({
        type: "GET",
        url: $('#url_refresh').val(),
        async: false,
        data: { unidad_id: $('#unidad_id').val(), tema_id: 0, evaluacion: 1 },
        dataType: "json",
        success: function(data) {
            if (data.ok == 1)
            {
                $('#sidebar_menu').html(data.sidebar_menu);
                $('#next').show();
            }
            else {
                $('#mensaje').html(data.msg);
                $('#modalerror').modal('show');
                //alert(data.msg);
            }
        },
        error: function(){
            $('#mensaje').html('Error refrescando menú');
            $('#modalerror').modal('show');
            //alert('Error refrescando menú');
        }
    });
}