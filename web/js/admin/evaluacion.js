$(document).ready(function() {

    $('#empresa_id').change(function(){
        var prueba_id = $('#prueba_id').val();
        var empresa_id = $('#empresa_id').val();
        var padre_pagina_id = '';
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        if (empresa_id != '')
        {
            selectDependientePagina(empresa_id, padre_pagina_id, entity, field_update, orderBy, prueba_id);
        }
    });

    $('#pagina_padre_id').change(function(){
        var prueba_id = $('#prueba_id').val();
        var empresa_id = $('#empresa_id').val();
        var padre_pagina_id = $(this).val();
        var field_update = $(this).attr('data');
        var entity = $(this).attr('entity');
        var orderBy = $(this).attr('orderBy');
        resetSelects(field_update);
        if (padre_pagina_id != '')
        {
            selectDependientePagina(empresa_id, padre_pagina_id, entity, field_update, orderBy, prueba_id);
        }
    });    

	$('.timePicker').timepicker({
	    timeFormat: 'H:mm',
	    interval: 15,
	    dynamic: false,
	    dropdown: true,
	    scrollbar: true
	});

});
