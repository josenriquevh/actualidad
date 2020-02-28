$(document).ready(function() {

    $('.delete').unbind('click');
    $('.delete').click(function(){
        var usuario_id = $(this).attr('data');
        sweetAlertDelete(usuario_id, 'AdminUsuario');
    });

});
