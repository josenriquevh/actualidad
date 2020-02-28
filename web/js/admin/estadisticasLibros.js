$(document).ready(function() {

    $('#fecha_desde').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var startDate = new Date(selected.date.valueOf());
    	$('#fecha_hasta').datepicker('setStartDate', startDate);
    });

     $('#fecha_hasta').datepicker({
	    startView: 1,
	    autoclose: true,
	    format: 'dd/mm/yyyy',
	    language: 'es',
	    clearBtn: true
	})
	.on( "changeDate", function(selected) {
		var endDate = new Date(selected.date.valueOf());
    	$('#fecha_desde').datepicker('setEndDate', endDate);
    });

    $('#grado_id').change(function(){
        var grado_id = $('#grado_id').val();
        var empresa_id = $('#empresa_id').val();
        $.ajax({
            type: "GET",
            url: $("#url_libros").val(),
            async: true,
            data: { grado_id: grado_id, empresa_id: empresa_id },
            dataType: "json",
            success: function(data) {
                $('#libros_list').html(data.html);
            },
            error: function(){
                $('#div-error-server').html($('#error-msg-paginas').val());
                notify($('#div-error-server').html());
            }
        });
    });

    $('#pdf').click(function(){
        $('#pdf').hide();
        $('#pdf-loader').show();
        renderIntoImage();
    });

    $('#search').click(function(){

        var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {

            $('#resultado').hide();
            $('#reporte').show();
            $('#load_r').show();
            $.ajax({
                type: "POST",
                url: $('#form').attr('action'),
                async: true,
                data: $("#form").serialize(),
                dataType: "json",
                success: function(data) {
                    if (data.ok == 1)
                    {
                        mostrarReporte(data.resultado);
                    }
                    else {
                        $('#div-error-server').html(data.msg);
                        notify($('#div-error-server').html());
                    }
                },
                error: function(){
                    $('#div-error-server').html($('#error-reporte').val());
                    notify($('#div-error-server').html());
                }
            });

        }
    });

});

function mostrarReporte(data)
{
    $('#load_r').hide();
    $('#resultado').show();
    $('.reporte').show();
    console.log(data);
    
    $('#unidades').html(data.reporte['unidades']);
    $('#temas').html(data.reporte['temas']);
    $('#actividades').html(data.reporte['actividades']);
    
    $('#sin_activar').html(data.reporte['sin_activar']);
    $('#sin_activar_pct').html(data.reporte['sin_activar_pct']);
    $('#activos').html(data.reporte['activos']);
    $('#activos_pct').html(data.reporte['activos_pct']);
    $('#total_1').html(data.reporte['total_1']);
    $('#total_1_pct').html(data.reporte['total_1_pct']);

    $('#no_iniciados').html(data.reporte['no_iniciados']);
    $('#no_iniciados_pct').html(data.reporte['no_iniciados_pct']);
    $('#en_curso').html(data.reporte['en_curso']);
    $('#en_curso_pct').html(data.reporte['en_curso_pct']);
    $('#finalizado').html(data.reporte['finalizado']);
    $('#finalizado_pct').html(data.reporte['finalizado_pct']);
    $('#total_2').html(data.reporte['total_2']);
    $('#total_2_pct').html(data.reporte['total_2_pct']);

    $('.reporte').show();

    // Gráfico 1
    var sin_activar_pct = data.reporte['sin_activar_pct'] != '-' ? data.reporte['sin_activar_pct'] : 0;
    var activos_pct = data.reporte['activos_pct'] != '-' ? data.reporte['activos_pct'] : 0;
    var datos1 = {
        type: "pie",
        data: {
            datasets: [{
                data: [
                    sin_activar_pct,
                    activos_pct
                ],
                backgroundColor: ["#0070c0", "#ed7d31"],
            }],
            labels: ['Inactivos', 'Activos']
        },
        options: {
            responsive: true,
            legend: {
                display: true,
                position: 'right'
            },
            title: {
                display: true,
                text: 'Estatus de códigos de libro ',
                position: 'top',
                fontSize: 14
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data1) {
                        var label = data1.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                        if (label) {
                            label += '%';
                        }
                        return label;
                    }
                }
            },
            pieceLabel: {
              render: 'percentage',
              fontColor: ['white', 'white']
            }
        }
    };
    var canvas1 = document.querySelector('#chart1').getContext('2d');
    window.pie1 = new Chart(canvas1, datos1);

    // Gráfico 2
    var no_iniciado_pct = data.reporte['no_iniciados_pct'] != '-' ? data.reporte['no_iniciados_pct'] : 0;
    var en_curso_pct = data.reporte['en_curso_pct'] != '-' ? data.reporte['en_curso_pct'] : 0;
    var finalizado_pct = data.reporte['finalizado_pct'] != '-' ? data.reporte['finalizado_pct'] : 0; 
    var datos2 = {
        type: "pie",
        data: {
            datasets: [{
                data: [
                    no_iniciado_pct,
                    en_curso_pct,
                    finalizado_pct
                ],
                backgroundColor: ["#ed7d31", "#ffc000", "#92d050"],
            }],
            labels: ['No iniciados', 'En curso', 'Aprobados']
        },
        options: {
            responsive: true,
            legend: {
                display: true,
                position: 'right'
            },
            title: {
                display: true,
                text: 'Avance de Participantes del libro ',
                position: 'top',
                fontSize: 14
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data2) {
                        var label = data2.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] || '0';
                        return label;
                    }
                }
            },
            pieceLabel: {
              render: 'percentage',
              fontColor: ['white', '#000000', 'white']
            }
        }
    };
    var canvas2 = document.querySelector('#chart2').getContext('2d');
    window.pie2 = new Chart(canvas2, datos2);
    
    $('#week_beforef').val(data.desde);
    $('#nowf').val(data.hasta);

}

const renderIntoImage = () => {

    // Función que transforma el gráfico en imagen
    for (var i=1; i<=2; i++)
    {


        const canvas = document.getElementById('chart'+i);
        var src_img = getImgFromCanvas(canvas);
        
        // Se almacena la imagen en el servidor
        $.ajax({
            type: "POST",
            url: $('#url_img').val(),
            async: true,
            data: "bin_data="+src_img+"&chart="+i,
            dataType: "json",
            success: function(response) {
                $('#pdf-loader').hide();
                var href = $("#url_pdf").val()+'/'+$('#empresa_id').val()+'/'+$('#pagina_id').val()+'/'+$('#week_beforef').val()+'/'+$('#nowf').val();
                $("#pdf-link").attr("href", href);
                $('#pdf-link').show();
            },
            error: function(){
                $('#div-error-server').html($('#error-msg').val());
                notify($('#div-error-server').html());
                $('.descargable').hide();
                $('.generable').show();
            }
        });
    }

}