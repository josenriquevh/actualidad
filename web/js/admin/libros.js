$(document).ready(function() 
{
	$( "#dtLibrosGrado" ).on( "click",".delete" , function (event)
		{
			event.preventDefault();
			
			
			
		});

	$( "#dtLibrosGrado" ).on( "click",".edit" , function (event)
		{
			event.preventDefault();
			
			
			
		});

	$('#grado_id').change(function()
	{
		
		var grado_id = $(this).val();
		if (grado_id>0) 
		{
			applyDataTable(grado_id);
			$('#tituloLibros').text('Listado de libros: '+$('#grado_id option:selected').text());
			$('#listaLibrosGrado').show();
		}
	});
});




