$(document).ready(function() {

    applyDataTable();

    $(".container_wizard").each(function(){
        var navListItems = $(this).find('div.setup-panel div a'),
            allWells = $(this).find('.setup-content'),
            allNextBtn = $('.nextBtn');
        allWells.hide();
        navListItems.click(function (e) {
            e.preventDefault();
            var $target = $($(this).attr('href')),
                $item = $(this);
            navListItems.removeClass('bttn__fndo').addClass('btn-secondary');
            $item.addClass('bttn__fndo');
            $item.removeClass('btn-secondary');
            $item.removeClass('disabled');
            allWells.hide();
            $target.show();
            $target.find('input:eq(0)').focus();
        });
        allNextBtn.click(function(){
            var curStep = $(this).closest(".setup-content"),
                curStepBtn = curStep.attr("id"),
                nextStepWizard = $('div.setup-panel div a[href="#' + curStepBtn + '"]').parent().next().children("a"),
                curInputs = curStep.find("input[type='text'],input[type='email']"),
                isValid = true;
            $(".form-group").removeClass("has-danger");
            for(var i=0; i<curInputs.length; i++){
                if (!curInputs[i].validity.valid){
                    isValid = false;
                    $(curInputs[i]).closest(".form-group").addClass("has-danger");
                }
            }
            if (isValid)
                nextStepWizard.removeAttr('disabled').trigger('click');
        });
        $(this).find('.setup-panel div a.bttn__fndo').trigger('click');
    });


    $("#edit, #list, #logout, #next").click(function(){
        var url = $(this).attr('id');
        var parameter = $(this).attr('parameter');
        var p = '';
        if (typeof url === 'undefined')
        {
            url = $(this).attr('data');
        }
        if (typeof parameter !== 'undefined')
        {
            p = '/'+parameter;
        }
        window.location.replace($('#url_'+url).val()+p);
    });

    //$('[data-toggle="tooltip"]').tooltip();

    $('.no-check').click(function(event){
        event.preventDefault();
        event.stopPropagation();
        return false;
    });

    $( ".columorden" )
          .mouseover(function() {
            $( '.columorden' ).css( 'cursor','move' );
          })
          .mouseout(function() {
            $( '.columorden' ).css( 'cursor','auto' );
    });

    $('#qr').click(function(){
        $.ajax({
            type: "POST",
            url: $('#form').attr('action'),
            async: true,
            data: $("#form").serialize(),
            dataType: "json",
            success: function(data) {
                $('#qri').html(data.ruta);
                $('#nombre').val("");
                $('#contenido').val("");
            }
        })
    });

    $('.close, #cancelar').click(function(){
        disableSubmit();
    });

    $( document ).tooltip({
        track: true
    });

    $('.form-control').focus(function(){
        $('#div-alert').hide();
    });

});

function initModalShow()
{
    $('#form').hide();
    $('#alert-success').show();
    $('#detail').show();
    $('#aceptar').show();
    $('#guardar').hide();
    $('#cancelar').hide();
}

function initModalEdit()
{
    $('label.error').hide();
    $('#form').show();
    $('#alert-success').hide();
    $('#detail').hide();
    $('#aceptar').hide();
    $('#cancelar').show();
    $('#div-alert').hide();
}

function enableSubmit()
{
    $('#guardar').show();
    $('#guardar').prop('disabled', false);
    //$('.form-control').prop('disabled', false);
    $('#form').safeform('complete');
}

function disableSubmit()
{
    $('#guardar').hide();
    $('#guardar').prop('disabled', true);
    //$('.form-control').prop('disabled', true);
}

// Función que transforma el gráfico en imagen
function getImgFromCanvas(canvas)
{

    var w = canvas.width;
    var h = canvas.height;
    var backgroundColor = '#FFFFFF';
    
    //get the current ImageData for the canvas.
    var context = canvas.getContext("2d");
    var data_img = context.getImageData(0, 0, w, h);
    
    //store the current globalCompositeOperation
    var compositeOperation = context.globalCompositeOperation;

    //set to draw behind current content
    context.globalCompositeOperation = "destination-over";

    //set background color
    context.fillStyle = backgroundColor;

    //draw background / rect on entire canvas
    context.fillRect(0,0,w,h);

    var imageData = canvas.toDataURL("image/png");

    //clear the canvas
    context.clearRect (0,0,w,h);

    //restore it with original / cached ImageData
    context.putImageData(data_img, 0,0);        

    //reset the globalCompositeOperation to what it was
    context.globalCompositeOperation = compositeOperation;

    src_img = imageData.replace('data:image/png;base64,', '');

    return src_img;

}

// Función que resetea el gráfico
function resetCanvas(canvas_id, canvas_class, container)
{
    $('#'+canvas_id).remove();
    $(container).html('<canvas class="'+canvas_class+'" id="'+canvas_id+'"><canvas>');
}

function selectDependiente(id, entity, field_update, reference, orderBy)
{
    $('#'+field_update).hide();
    $('#loader-'+field_update).show();
    $.ajax({
        type: "GET",
        url: $("#url_select").val(),
        async: true,
        data: { id: id, entity: entity, reference: reference, orderBy: orderBy },
        dataType: "json",
        success: function(data) {
            $('#'+field_update).html(data.options);
            $('#'+field_update).show();
            $('#loader-'+field_update).hide();
        },
        error: function(){
            $('#div-error-server').html($('#error-msg-'+field_update).val());
            notify($('#div-error-server').html());
        }
    });
}

function selectDependientePagina(empresa_id, pagina_id, entity, field_update, orderBy, prueba_id=0)
{
    $('#'+field_update).hide();
    $('#loader-'+field_update).show();
    $.ajax({
        type: "GET",
        url: $("#url_select_pagina").val(),
        async: true,
        data: { empresa_id: empresa_id, pagina_id: pagina_id, entity: entity, orderBy: orderBy, prueba_id: prueba_id },
        dataType: "json",
        success: function(data) {
            $('#'+field_update).html(data.options);
            $('#'+field_update).show();
            $('#loader-'+field_update).hide();
        },
        error: function(){
            $('#div-error-server').html($('#error-msg-'+field_update).val());
            notify($('#div-error-server').html());
        }
    });
}
function resetSelects(field_update)
{
    var reset_update = $('#'+field_update).attr('reset');
    var resets = $('#resets').val();
    if (reset_update <= resets)
    {
        for (var i=reset_update; i<=resets; i++)
        {
            $('.reset'+i).html('<option value=""></option>');
        }
    }
}
