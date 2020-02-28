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
          if(data.ok==1){
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
        success: function(datos) {
          if(datos.ok==1){
              $('#titulo_libro').text(datos.titulo); 
              $('#load1').hide();
              $('#div-grafico').show();
              $('#fila-grafico').show(); 
              $('#pdf').show();
            	$(function() {   
              var ctx = document.getElementById("barChart");
              window.barChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                    labels: datos.labels,
                    datasets: [{
                      label: datos.label,
                      data: datos.data,
                      backgroundColor: datos.colors,
                      borderColor: datos.colors,
                      borderWidth: 1
                    }]
                  },
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
            $('#pdf').show();
              
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
   })
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
            data: "bin_data="+src_img+"&reporte="+"codigosUbicacion",
            dataType: "json",
            success: function(response) {
                $('#pdf-loader').hide();
                var href = $("#url_pdf").val()+'/'+$('#libro_id').val()+'/0/0';
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


