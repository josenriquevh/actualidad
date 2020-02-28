<?php

namespace Actualidad\ComunBundle\Services;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;


class Reportes
{	
	
	protected $em;
	protected $container;
	protected $mailer;
	private $templating;
	private $translator;

    public function __construct(\Doctrine\ORM\EntityManager $em, ContainerInterface $container)
	{

		$this->em = $em;
		$this->container = $container;
		$this->mailer = $container->get('mailer');
        $this->templating = $container->get('templating');
        $this->translator = $container->get('translator');
	}

    
	public function estadisticaLibros($libro_id, $fecha_desde, $fecha_hasta)
	{
		$em = $this->em;
		$resultados = array();


        $query = $em->getConnection()->prepare('SELECT
                                                fnbook_stats(:ppagina_id, :pfecha_inicio, :pfecha_fin) as
                                                resultado;');
        $query->bindValue(':ppagina_id', $libro_id, \PDO::PARAM_INT);
        $query->bindValue(':pfecha_inicio', $fecha_desde, \PDO::PARAM_STR);
        $query->bindValue(':pfecha_fin', $fecha_hasta, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        $r_arr = explode("__", $r[0]['resultado']);
        $resultados['unidades'] = (int) $r_arr[0];
        $resultados['temas'] = (int) $r_arr[1];
        $resultados['actividades'] = (int) $r_arr[2];
        $resultados['activos'] = (int) $r_arr[3];
        $resultados['sin_activar'] = (int) $r_arr[4];
        $resultados['no_iniciados'] = (int) $r_arr[5];
        $resultados['en_curso'] = (int) $r_arr[6];
        $resultados['finalizado'] = (int) $r_arr[7];
        $resultados['total_1'] = $resultados['activos'] + $resultados['sin_activar'];
        $resultados['total_2'] = $resultados['no_iniciados'] + $resultados['en_curso'] + $resultados['finalizado'];

        $sin_activar_pct = $resultados['total_1'] != 0 ? ($resultados['sin_activar']/$resultados['total_1'])*100 : '-';
        $resultados['sin_activar_pct'] = $sin_activar_pct != '-' ? number_format($sin_activar_pct, 2, '.', ',') : $sin_activar_pct;

        $activos_pct = $resultados['total_1'] != 0 ? ($resultados['activos']/$resultados['total_1'])*100 : '-';
        $resultados['activos_pct'] = $activos_pct != '-' ? number_format($activos_pct, 2, '.', ',') : $activos_pct;

        $resultados['total_1_pct'] = $resultados['total_1'] != 0 ? 100 : '-';

        $no_iniciados_pct = $resultados['total_2'] != 0 ? ($resultados['no_iniciados']/$resultados['total_2'])*100 : '-';
        $resultados['no_iniciados_pct'] = $no_iniciados_pct != '-' ? number_format($no_iniciados_pct, 2, '.', ',') : $no_iniciados_pct;

        $en_curso_pct = $resultados['total_2'] != 0 ? ($resultados['en_curso']/$resultados['total_2'])*100 : '-';
        $resultados['en_curso_pct'] = $en_curso_pct != '-' ? number_format($en_curso_pct, 2, '.', ',') : $en_curso_pct;

        $finalizado_pct = $resultados['total_2'] != 0 ? ($resultados['finalizado']/$resultados['total_2'])*100 : '-';
        $resultados['finalizado_pct'] = $finalizado_pct != '-' ? number_format($finalizado_pct, 2, '.', ',') : $finalizado_pct;

        $resultados['total_2_pct'] = $resultados['total_2'] != 0 ? 100 : '-';

        return $resultados;
	}


    public function profesoresActivos(){
        $em = $this->em;
        $return = array();
        $fecha_actual = date('Y').'/'.date('m').'/'.date('d');

        $query = $em->createQuery("SELECT u.id as profesor_id, u.login as username, u.nombre as nombre, u.apellido as apellido,u.correo as                             correo,u.activo as activo, u.fechaCreacion as fecha_registro, p.nombre as provincia, c.nombre as ciudad,
                                          MAX(s.fechaIngreso) as ultima_conexion
                                          FROM ActualidadComunBundle:AdminUsuario u
                                          LEFT JOIN ActualidadComunBundle:AdminProvincia p WITH p.id = u.provincia
                                          LEFT JOIN ActualidadComunBundle:AdminCiudad c WITH c.id = u.ciudad
                                          LEFT JOIN ActualidadComunBundle:AdminSesion s WITH s.id = u.id
                                    WHERE u.activo = :activo 
                                    AND u.rol = :rol
                                    GROUP BY u.id,u.login,u.nombre,u.apellido,u.correo,u.activo,u.fechaCreacion,p.nombre,c.nombre")
                     ->setParameters(array('activo'=> TRUE,'rol'=>3));
        $rs = $query->getResult();

        foreach ($rs as $profesor){
                $resultado=array();

                //libros del profesor
                $query = $em->createQuery("SELECT p.titulo as libro, p.id as libro_id, t.nombre as tipo  FROM ActualidadComunBundle:EaPaginaUsuario pu
                                          INNER JOIN ActualidadComunBundle:EaPagina p WITH p.id = pu.pagina
                                          INNER JOIN ActualidadComunBundle:EaTipoPagina t WITH t.id = p.tipoPagina
                                          WHERE pu.usuario = :profesor_id
                                          AND :fecha_actual BETWEEN pu.fechaInicio AND pu.fechaVencimiento
                                          GROUP BY p.titulo,p.id ,t.nombre")
                    ->setParameters(array('profesor_id'=> $profesor['profesor_id'],'fecha_actual'=> $fecha_actual));
                $libros = $query->getResult();
                
 
                //colegios del profesor
                $query = $em->createQuery("SELECT c.nombre as colegio, c.id as colegio_id, p.nombre as ciudad FROM ActualidadComunBundle:AdminUsuarioSeccion us
                                          INNER JOIN ActualidadComunBundle:AdminSeccion s WITH s.id = us.seccion
                                          INNER JOIN ActualidadComunBundle:AdminColegio c WITH c.id = s.colegio
                                          INNER JOIN ActualidadComunBundle:AdminCiudad  p WITH p.id = c.ciudad
                                          WHERE us.usuario = :profesor_id
                                          GROUP BY c.id,c.nombre,p.nombre")
                    ->setParameter('profesor_id', $profesor['profesor_id']);
                $colegios = $query->getResult();
                
                $resultado['id'] = $profesor['profesor_id'];
                $resultado['usuario'] = $profesor['username'];
                $resultado['nombre']  =$profesor['nombre'];
                $resultado['apellido'] = $profesor['apellido'];
                $resultado['profesor'] = $profesor['ciudad'];
                $resultado['fecha_registro'] = $profesor['fecha_registro'];
                $resultado['correo'] = $profesor['correo'];
                $resultado['ciudad'] = $profesor['ciudad'];
                $resultado['conexion'] = $profesor['ultima_conexion'];
                $resultado['provincia'] = $profesor['provincia'];
                $resultado['colegios'] = $colegios;
                $resultado['libros'] = $libros;
                
                array_push($return,$resultado);

            }

        return $return;

    }
}
