var localhost = $('#localhost').val();

$(document).ready(function() {

    observeResources();

    $('.continuar').click(function(){

        // Stop audio o videos en reproducción
        $('video').trigger('pause');
        $('audio').trigger('pause');
        if (localhost != 1)
        {
            postMessagePause();
        }

        $('.next').addClass('blocked');

        setTimeout(function() {
            // Se determina la siguiente pagina_id
            var pagina_id = 0;
            var total_recursos = $('#total_recursos').val();
            var total_temas = $('#total_temas').val();
            var pagina_id_actual = $('#pagina_id').val();
            var tema_id_actual = $('#tema_id').val();
            var orden_recurso_actual = $('#ordenRecurso'+pagina_id_actual).val();
            if (parseInt(orden_recurso_actual,10) < parseInt(total_recursos,10))
            {
                var orden_recurso_siguiente = parseInt(orden_recurso_actual)+parseInt(1);
                pagina_id = $('#rOrder'+orden_recurso_siguiente).val();
            }
            else {
                // Se determina el recurso que aún no se ha visto
                for (var r=1; r<=total_recursos; r++)
                {
                    var r_id = $('#rOrder'+r).val();
                    var r_visto = $('#recursoVisto'+r_id).val();
                    if (r_visto == 0)
                    {
                        pagina_id = r_id;
                        break;
                    }
                }
            }

            if (pagina_id == 0)
            {

                // Redireccionamiento al próximo tema o a la evaluación
                var tema_id = 0;
                var orden_tema_actual = $('#ordenTema'+tema_id_actual).val();
                if (parseInt(orden_tema_actual,10) < parseInt(total_temas,10))
                {
                    var orden_tema_siguiente = parseInt(orden_tema_actual)+parseInt(1);
                    tema_id = $('#tOrder'+orden_tema_siguiente).val();
                }
                else {
                    // Se determina el tema que aún no se ha visto
                    for (var t=1; t<=total_temas; t++)
                    {
                        var t_id = $('#tOrder'+t).val();
                        var t_visto = $('#temaVisto'+t_id).val();
                        if (t_visto == 0)
                        {
                            tema_id = t_id;
                            break;
                        }
                    }
                }

                if (tema_id == 0)
                {
                    if ($('#tiene_evaluacion').val() == 0)
                    {
                        // Redireccionamiento a las unidades
                        window.location.replace($('#url_unidades').val());
                    }
                    else {
                        // Redireccionamiento a la evaluación
                        window.location.replace($('#url_evaluacion').val());
                    }
                }
                else {
                    // Redireccionamiento al siguiente tema
                    window.location.replace($('#url_temas').val()+'/'+tema_id);
                }

            }
            else {
                // Mostrar el próximo recurso
                $('#resourse-content').hide();
                $('#controls').hide();
                $('#loader').show();
                getResource(pagina_id);
            }
        }, 2000);

    });

    $('.prev').click(function(){
        
        // Stop audio o videos en reproducción
        $('video').trigger('pause');
        $('audio').trigger('pause');
        if (localhost != 1)
        {
            postMessagePause();
        }

        $('.prev').addClass('blocked');
        $('.next').addClass('blocked');

        setTimeout(function() {
            // Se determina la anterior pagina_id
            var pagina_id = 0;
            var pagina_id_actual = $('#pagina_id').val();
            var orden_recurso_actual = $('#ordenRecurso'+pagina_id_actual).val();
            var orden_recurso_anterior = parseInt(orden_recurso_actual)-parseInt(1);
            pagina_id = $('#rOrder'+orden_recurso_anterior).val();

            if (pagina_id != 0)
            {
                // Mostrar el recurso
                $('#resourse-content').hide();
                $('#controls').hide();
                $('#loader').show();
                getResource(pagina_id);
            }
        }, 2000);

    });

    $('#closeInt').click(function(){
        // Stop audio o videos en reproducción
        $('video').trigger('pause');
        $('audio').trigger('pause');
        if (localhost != 1)
        {
            postMessagePause();
        }
    });
    
});

function observeResources()
{
    $('.resource').click(function(){
        
        // Stop audio o videos en reproducción
        $('video').trigger('pause');
        $('audio').trigger('pause');

        var pagina_id = $(this).attr('data');

        $('.next').addClass('blocked');
        $('#resourse-content').hide();
        $('#controls').hide();
        $('#loader').show();

        getResource(pagina_id);

    });
}

function getResource(pagina_id)
{
    // Obtener el recurso del servidor
    $('#pagina_id').val(pagina_id);
    setTimeout(function() {
        $.ajax({
            type: "GET",
            url: $('#url_resource').val(),
            async: false,
            data: { pagina_id: pagina_id, total_recursos: $('#total_recursos').val() },
            dataType: "json",
            success: function(data) {
                if (data.ok == 1)
                {

                    $('#resourse-content').html(data.contenido);
                    $('.countInt').html(data.counter);
                    $('#recurso_actual').val(data.recurso_actual);
                    $('#resourse-content').show();
                    $('#controls').show();
                    $('#loader').hide();

                    // GIF para el helper
                    var img_gif = $('#helpergif');
                    if (data.gif != '')
                    {
                        img_gif.attr('src', data.gif);
                    }
                    else {
                        $('#help').show();
                        img_gif.attr('src', $('#img_gif').val());
                    }

                    if (data.visto == 1)
                    {
                        $('#recursoVisto'+pagina_id).val(1);
                        $('.next').removeClass('blocked');
                    }
                    else {
                        $('#recursoVisto'+pagina_id).val(0);
                        $('.next').addClass('blocked');
                    }

                    if (data.recurso_actual == 1)
                    {
                        $('.prev').addClass('blocked');
                    }
                    else {
                        $('.prev').removeClass('blocked');
                    }

                    if (localhost != 1)
                    {
                        // Solo cuando se ejecuta en servidores se valida el postmessage
                        if (data.interactivo == 1)
                        {
                            $('#es_interactivo').val(1);
                            // Aparecer helper
                            $('#help').show();
                            $('#help-close').show();
                            postMessage(pagina_id);
                        }
                        else {
                            $('#es_interactivo').val(0);
                            // Esconder helper
                            $('#help').hide();
                            $('#help-close').hide();
                            observeVideo();
                        }
                    }
                    else {
                        setTimeout(function() {
                            finishResource();
                        }, 10000);
                    }

                }
                else if (data.ok == 3) {
                    var r = confirm("La sesion a expirado");
                    if (r == true){
                        window.location.replace($('#url_login').val());
                    }else{

                    }
                }
                else {
                    $('#mensaje').html(data.msg);
                    $('#modalerror').modal('show');
                    //alert(data.msg);
                }
            },
            error: function(){
                $('#mensaje').html('Error obteniendo el recurso');
                $('#modalerror').modal('show');
                //alert('Error obteniendo el recurso');
            }
        });
    }, 3000);
}

function postMessage(pagina_id)
{
    clearInterval($('#timer').val());
    var iframe = document.getElementById('ifr');
    if (iframe !== null)
    {
        var timer = setInterval(function(){
            console.log('Llamada al postmessage');
            iframe.contentWindow.postMessage({
                usuario_id: $('#usuario_id').val(),
                url: $('#url_servicio').val(),
                make_ajax: $('#make_ajax').val(),
                pagina_id: pagina_id,
                dominio_origen: $('#dominio_origen').val(),
                stop_media: 0
            }, $('#servidor_recursos').val());
        }, 5000);
        $('#timer').val(timer);
    }
    else {
        alert('No existe iframe con id ifr');
    }
}

function postMessagePause()
{
    if ($('#es_interactivo').val() == 1)
    {
        var iframe = document.getElementById('ifr');
        if (iframe !== null)
        {
            console.log('Llamada al postmessage por pause');
            iframe.contentWindow.postMessage({
                usuario_id: $('#usuario_id').val(),
                url: $('#url_servicio').val(),
                make_ajax: $('#make_ajax').val(),
                pagina_id: 0,
                dominio_origen: $('#dominio_origen').val(),
                stop_media: 1
            }, $('#servidor_recursos').val());
        }
        else {
            alert('No existe iframe con id ifr');
        }
    }
}

function receiveMessage(event)
{
    // Do we trust the sender of this message?
    if (event.origin !== $('#servidor_recursos').val())
        return;

    var ok = event.data.ok;
    clearInterval($('#timer').val()); // Parar el timer del postMessage

    console.log('ok:'+ok);

    if (ok == 1)
    {
        console.log('Se da por completado el interactivo, habilitar el botón de CONTINUAR.');
        setTimeout(function() {
            refreshMenu();
        }, 3000);
    }
    else {
        $('.next').addClass('blocked');
    }

}

window.addEventListener("message", receiveMessage, false);

function observeVideo()
{
    var video = document.getElementById('ifr');
    if (video !== null)
    {
        video.addEventListener("ended", finishResource);
    }
    else {
        alert('No existe video con id ifr');
    }
}

function finishResource()
{
    var pagina_id = $('#pagina_id').val();
    $.ajax({
        type: "POST",
        url: $('#url_finishResource').val(),
        async: false,
        data: { pagina_id: pagina_id },
        dataType: "json",
        success: function(data) {
            if (data.ok == 1)
            {
                refreshMenu();
            }
            else if (data.ok == 3) {
                var r = confirm("La sesion a expirado");
                if (r == true){
                    window.location.replace($('#url_login').val());
                }else{

                }
            }
            else {
                $('#mensaje').html(data.msg);
                $('#modalerror').modal('show');
                //alert(data.msg);
            }
        },
        error: function(){
            $('#mensaje').html('Error procesando el recurso');
            $('#modalerror').modal('show');
            //alert('Error procesando el recurso');
        }
    });
}

function refreshMenu()
{
    $.ajax({
        type: "GET",
        url: $('#url_refresh').val(),
        async: false,
        data: { unidad_id: $('#unidad_id').val(), tema_id: $('#tema_id').val(), evaluacion: $('#evaluacion').val() },
        dataType: "json",
        success: function(data) {
            if (data.ok == 1)
            {
                $('#sidebar_menu').html(data.sidebar_menu);
                $('.cards-int').html(data.cards);
                observeResources();
                setTimeout(function() {
                    $('.next').removeClass('blocked');
                }, 5000);
            }
            else if (data.ok == 3){
                var r = confirm("La sesion a expirado");
                if (r == true){
                    window.location.replace($('#url_login').val());
                }else{

                }
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