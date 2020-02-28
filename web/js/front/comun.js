function selectDependiente(id, entity, field_update, reference, orderBy)
{
    $('#'+field_update).hide();
    $('#loader-'+field_update).show();
    $('.color-error').hide();
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
            $('#selectdiv-'+field_update+', #'+field_update).removeClass('input-disabled');
        },
        error: function(){
            $('#'+field_update+'-error').html($('#error-msg-'+field_update).val());
            $('.color-error').show();
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
            var select_input = $('.reset'+i);
            var select_name = select_input.attr('name');
            $('#selectdiv-'+select_name+', #'+select_name).addClass('input-disabled');
        }
    }
}
