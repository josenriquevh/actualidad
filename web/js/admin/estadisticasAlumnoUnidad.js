$(document).ready(function() {

    $('.selDepend').change(function(){
        
        var dependId = $(this).attr('data-depend');
        var dependVal = $('#'+dependId).val();
        $('#unidad_id').html('');
        
        if ( dependVal !="" && $(this).val()!=""){
            var empresa_id = $('#empresa_id').val();
            var grado_id = $('#grado_id').val();
            $('#img-loader-libro').show();
            $.ajax({
                type: "POST",
                url: $('#url_libros_foro').val(),
                async: true,
                data: {empresa_id: empresa_id, grado_id: grado_id },
                dataType: "json",
                success: function(data) {
                    if(data.ok == 1) {
                        $('#libro_id').html('');
                        $('#img-loader-libro').hide();
                        if(data.cnt == 0){
                            notify('','warning','<b>'+$('#alert-msg-Ldisponibles').val()+'</b>');
                        }
                        else{
                          $('#libro_id').html(data.libros);
                        }
                    }
                    else{
                        notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
                    }
                },
                error: function(){
                    $('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
                    notify('','danger',$('#div-error').html());
                }
            });
        }
    });

    $('#form').submit(function(e) {
        e.preventDefault();
    });
  
    $('#search').click(function(){
        var valid = $("#form").valid();
        if (!valid) 
        {
            notify($('#div-error').html());
        }
        else {
         
          $('#form').submit();
            return false; 
        }
    });

    $('#form').safeform({    
        submit: function(e) {
            $('#div-grafico').show();
            $('#load1').show();
            $('#panelTitle').hide();
            $('#fila-grafico').hide();  
            $('#pdf').hide(); 
            $('#pdf-link').hide(); 
            $('#titulo_libro').text(''); 
            $('#canvasCont1').html('<canvas id="barChart" width="1000" height="400"></canvas>');
            $.ajax({
                type: "POST",
                url: $('#form').attr('action'),
                async: true,
                data: $("#form").serialize(),
                dataType: "json",
                success: function(data) {
                    if(data.ok == 1){
                        $('#codigos_activos').text(data.codigos_activos);
                        $('#titulo_libro').text(data.titulo_libro);
                        $('#load1').hide();
                        $('#panelTitle').show();
                        $('#div-grafico').show();
                        $('#fila-grafico').show(); 
                        $('#pdf').show();
                        $(function() {   
                            var ctx = document.getElementById("barChart");
                            var dataIniciados = {
                                label: 'Iniciados',
                                data: data.iniciados,
                                backgroundColor: '#03A9F4',
                                borderColor: '#03A9F4'
                            };

                            var dataFinalizados = {
                                label: 'Finalizados',
                                data: data.finalizados,
                                backgroundColor: '#8BC34A',
                                borderColor: '#8BC34A'
                            };

                            var dataPendientes = {
                                label: 'No iniciados',
                                data: data.pendientes,
                                backgroundColor: '#F44336',
                                borderColor: '#F44336'
                            };

                            var graphData = {
                                labels: data.labels,
                                datasets: [dataIniciados, dataFinalizados, dataPendientes ]
                            };
                            //console.log(graphData);
                            window.barChart = new Chart(ctx, {
                                type: 'bar',
                                data: graphData,
                                options: {
                                    responsive: true,
                                    scales: {
                                        xAxes: [{
                                            ticks: {
                                                maxRotation: 90,
                                                minRotation: 80
                                            }
                                        }],
                                        yAxes: [{
                                            ticks: {
                                                beginAtZero: true
                                            }
                                        }]
                                    }
                                }
                            });  
                        });
                    }
                    else{
                        notify('<BR>'+data.msg,'danger','<B>'+$('#error-msg-server').val()+'</B>');
                    }
                    $('#form').safeform('complete');
                    return false;
                },
                error: function(){
                    $('#div-error').html('<B>'+$('#error-msg').val()+'</B>');
                    notify('','danger',$('#div-error').html());
                    $('#form').safeform('complete');
                    return false;
                }
            });
        }
    });

    $('#pdf').click(function(){
        $('#pdf').hide();
        $('#pdf-loader').show();
        renderImage();
    });

});

const renderImage = () => {

    // Función que transforma el gráfico en imagen
    var canvas = document.getElementById('barChart');
    var src_img = getImgFromCanvas(canvas);
    
    // Se almacena la imagen en el servidor
    $.ajax({
        type: "POST",
        url: $('#url_img_ub').val(),
        async: true,
        data: "bin_data="+src_img+"&reporte="+"estadisticasUnidades",
        dataType: "json",
        success: function(response) {
            $('#pdf-loader').hide();
            var href = $("#url_pdf").val()+'/'+$('#libro_id').val()+'/1/1/'+$('#codigos_activos').text();
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
