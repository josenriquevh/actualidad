<?php

// actualidad/src/Actualidad/BackendBundle/Command/UsersScheduledCommand.php

namespace Actualidad\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\Parameter;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Actualidad\ComunBundle\Entity\AdminCorreo;

class UsersScheduledCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ea:recordatorio-programados')
        	 ->setDescription('Envía por correo notificaciones programadas y recordatorios de la fecha actual');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $base = $yml['parameters']['url_plataforma'];
        
        $hoy = date('Y-m-d');
        $query = $em->getConnection()->prepare('SELECT fnrecordatorios_usuarios(:pfecha) AS resultado;');
        $query->bindValue(':pfecha', $hoy, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        //error_log('-------------------CRON JOB DEL DIA '.date('d/m/Y H:i').'---------------------------------------------------');
        $output->writeln('CANTIDAD: '.count($r));

        $titulo = 'Notificación';
        $footer = $yml['parameters']['folders']['uploads'].'footernewsletter.png';
        $logo = $yml['parameters']['folders']['uploads'].'logo-actualidad-light.png';
        $j = 0; // Contador de correos exitosos
        $np_id = 0; // notificacion_programada_id

        for ($i = 0; $i < count($r); $i++) 
        {

            if ($j == 100)
            {
                // Cantidad tope de correos en una corrida
                break;
            }

            // Limpieza de resultados
            $reg = substr($r[$i]['resultado'], strrpos($r[$i]['resultado'], '{"')+2);
            $reg = str_replace('"}', '', $reg);

            // Se descomponen los elementos del resultado
            list($np_id, $usuario_id, $login, $clave, $nombre, $apellido, $correo, $asunto, $mensaje) = explode("__", $reg);

            if ($correo != '')
            {

                // Validar que no se haya enviado el correo a este destinatario
                $correo_bd = $em->getRepository('ActualidadComunBundle:AdminCorreo')->findOneBy(array('tipoCorreo' => $yml2['parameters']['tipo_correo']['notificacion_programada'],
                                                                                                      'entidadId' => $np_id,
                                                                                                      'usuario' => $usuario_id,
                                                                                                      'correo' => $correo));

                if (!$correo_bd)
                {

                    // Sustitución de variables en el texto
                    $comodines = $yml2['parameters']['comodines_correo'];
                    $reemplazos = array($login, $clave, $nombre, $apellido);
                    $mensaje = str_replace($comodines, $reemplazos, $mensaje);

                    $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                               'datos' => array('nombre' => $nombre,
                                                                'apellido' => $apellido,
                                                                'mensaje' => $mensaje,
                                                                'footer' => $footer,
                                                                'logo' => $logo,
                                                                'titulo' => $titulo,
                                                                'url_plataforma' => $base),
                                               'asunto' => $asunto,
                                               'remitente' => $yml['parameters']['mailer_user'],
                                               'remitente_name' => $yml['parameters']['mailer_user_info_name'],
                                               'destinatario' => $correo,
                                               'mailer' => 'info_mailer');
                    $ok = $f->sendEmail($parametros_correo);

                    if ($ok)
                    {

                        $j++;

                        $output->writeln($j.' .----------------------------------------------------------------------------------------------');
                        $output->writeln('np_id: '.$np_id);
                        $output->writeln('usuario_id: '.$usuario_id);
                        $output->writeln('Usuario: '.$nombre.' '.$apellido);
                        $output->writeln('Correo enviado a '.$correo);

                        // Registro del correo recien enviado
                        $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['notificacion_programada']);
                        $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($usuario_id);
                        $email = new AdminCorreo();
                        $email->setTipoCorreo($tipo_correo);
                        $email->setEntidadId($np_id);
                        $email->setUsuario($usuario);
                        $email->setCorreo($correo);
                        $email->setFecha(new \DateTime('now'));
                        $em->persist($email);
                        $em->flush();

                    }
                    else {
                        //error_log(' .----------------------------------------------------------------------------------------------');
                        //error_log($reg);
                        //error_log('NO SE ENVIO '.$correo);
                    }

                }

            }
            
        }

    }
}