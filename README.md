## Instalación

1. En el servidor web debe existir los siguientes subdirectorios, con permisos de escritura:
```
  uploads/
    actualidad/
      paginas/
      noticias/
      usuarios/
      evaluaciones/
      codigos/
      reportes/
      espacio/
      tutoriales/
      certificados/
      decorate_certificado.png	# Tomar la imagen de web/front/assets/img/
```
1. Las constantes globales para twig están seteadas en `app/config/config.yml`.
1. Las constantes para controllers están seteadas en `app/config/parametros.yml`.
1. Los parámetros de conexión a base de datos y servidor de correo están seteadas en `app/config/parameters.yml`.
1. Versión mínima de PHP que debe estar instalada: 5.6.25.
1. Base de datos con Postgres 9.4.
1. Activar las siguientes extensiones PHP php.ini, tanto en el del apache como el del php:
    - php_curl
    - php_intl
    - php_pdo_pgsql
    - php_pgsql
1. Importar los siguientes sql en la consola de postgres, siguiendo este orden:
    - definition.sql
    - indices.sql
    - listas.sql
    - funciones/fnactivos.sql
    - funciones/fnbook_stats.sql
    - funciones/fncantidad_programados.sql
    - funciones/fncerrar_sesiones.sql
    - funciones/fncodigos_ubicacion.sql
    - funciones/fnejemplares_libros.sql
    - funciones/fnestadisticas_colegios.sql
    - funciones/fnestadisticas_libros.sql
    - funciones/fngenerar_codigos.sql
    - funciones/fningresos_sistema.sql
    - funciones/fninteraccion_unidades.sql
    - funciones/fnlibros_activos.sql
    - funciones/fnnotificacion_programada.sql
    - funciones/fnrecordatorios_usuarios.sql
    - funciones/fnunidades_recientes.sql
1. Setear las siguientes variables en php.ini:
```
  memory_limit = 128M
  post_max_size = 80M
  upload_max_filesize = 80M
  max_file_uploads = 20
  realpath_cache_size = 5M
  xdebug.var_display_max_depth = -1 
  xdebug.var_display_max_children = -1
  xdebug.var_display_max_data = -1
  allow_url_fopen = On
```
1. Para la configuración del CKFinder, ajustar los valores en `web/assets/vendor/ckfinder/config.php`.
1. URL backend: http://tudominio/actualidad/web/app_dev.php/admin/

---
