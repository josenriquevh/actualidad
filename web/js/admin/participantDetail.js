$(document).ready(function() {
	observeList();
});

function setDetails(data)
{
	$('#loadDetail').hide();
	$('#contenidoDetail').show();
	var img = $('#foto');
	if (data.usuario.foto != 0)
	{
		var uploads = $('#uploads').val();
		img.attr("src", uploads+data.usuario.foto);
	}
	else {
		img.attr("src", $('#profilePhoto').val());
	}

	$('#login').val(data.usuario.login);
	$('#nombre').val(data.usuario.nombre);
	$('#apellido').val(data.usuario.apellido);
	$('#correoPersonal').val(data.usuario.correoPersonal);
	$('#fechaNacimiento').val(data.usuario.fechaNacimiento);
	$('#activo').html(data.usuario.activo);
	$('#rol').val(data.usuario.rol);
	$('#provincia').val(data.usuario.provincia);
	$('#ciudad').val(data.usuario.ciudad);
	$('#colegios').val(data.usuario.colegios);
	$('#campo4').val(data.usuario.campo4);
	$('#primeraConexion').val(data.usuario.ingresos.primeraConexion);
	$('#ultimaConexion').val(data.usuario.ingresos.ultimaConexion);
	$('#cantidadConexiones').val(data.usuario.ingresos.cantidadConexiones);
	$('#promedioConexion').val(data.usuario.ingresos.promedioConexion);
	$('#noIniciados').val(data.usuario.ingresos.noIniciados);
	$('#enCurso').val(data.usuario.ingresos.enCurso);
	$('#finalizados').val(data.usuario.ingresos.finalizados);;
}



function observeList()
{
	$('.detail').unbind('click');
	$('.detail').click(function(){
		$('#reporteDetail').show();
		$('#contenidoDetail').hide();
    	$('#loadDetail').show();
    	$('#headerDetail').hide();
    	$('#div-alert-detail').hide();
    	var usuario_id = $(this).attr('data');
    	var empresa_id = $(this).attr('empresa_id');
		$.ajax({
			type: "POST",
			url: $('#url_detail').val(),
			async: true,
			data: { empresa_id: empresa_id, usuario_id: usuario_id },
			dataType: "json",
			success: function(data) {
				setDetails(data);
			},
			error: function(){
				$('#div-alert-detail').show();
				$('#loadDetail').hide();
			}
		});
	});

}
