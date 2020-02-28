$(document).ready(function() {
	
	var root_site = $('#root_site').val();

    CKEDITOR.replace( 'descripcion', {
    	filebrowserUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files&currentFolder=/evaluaciones/',
	    filebrowserImageUploadUrl: root_site+'/assets/vendor/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images&currentFolder=/evaluaciones/',
		on: {
			instanceReady: function() {
				var editor_data = CKEDITOR.instances.descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			},
			key: function() {
				var editor_data = CKEDITOR.instances.descripcion.getData();
				var elem = document.getElementById("deslen");
				elem.value = parseInt(editor_data.replace(/<[^>]+>/g, '').length);
			}
		}
	} );

	$('.nextBtn, stepwizard-step').click(function(){
		// Cantidad de caracteres en el resumen
		var editor_descripcion = CKEDITOR.instances.descripcion.getData();
		var deslen = document.getElementById("deslen");
		deslen.value = parseInt(editor_descripcion.replace(/<[^>]+>/g, '').length);
	});

});