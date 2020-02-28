<?php

// actualidad/src/Actualidad/BackendBundle/Command/ExpiredBooksCommand.php

namespace Actualidad\BackendBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Doctrine\ORM\Query\Parameter;
use Doctrine\DBAL\Connection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;
use Actualidad\ComunBundle\Entity\AdminCorreo;
use Actualidad\ComunBundle\Entity\AdminAlarma;
use Symfony\Component\Yaml\Yaml;

class ExpiredBooksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ea:aviso-vencimiento')
             ->setDescription('Se envia un correo y se crea una notificacion cuando a un libro activo le quede 1 mes, 15 dias o 2 dias para vencerse');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();
        $f = $this->getApplication()->getKernel()->getContainer()->get('funciones');
        $yml = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parameters.yml'));
        $yml2 = Yaml::parse(file_get_contents($this->getApplication()->getKernel()->getRootDir().'/config/parametros.yml'));
        $translator = $this->getContainer()->get('translator');
        $base = $yml['parameters']['url_plataforma'];
        $tipo_alarma = $em->getRepository('ActualidadComunBundle:AdminTipoAlarma')->find($yml2['parameters']['tipo_alarma']['codigo_expirar']);

        // Llamada a la función que verifica aquellos libros que le falta 1 mes para vencerse
        $hoy = date('Y-m-d');
        $fecha_inicio = $hoy.' 00:00:00';
        $fecha_fin = $hoy.' 23:59:59';
        $fecha = new \DateTime();
        $fecha->modify('+1 month');
        $fecha_v = $fecha->format('Y-m-d');
        
        

        $query = $em->getConnection()->prepare('SELECT
                                                    fnaviso_vencimiento(:re, :pfecha) as
                                                    resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pfecha', $fecha_v, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        $titulo = $translator->trans('Aviso de vencimiento');
        $footer = $yml['parameters']['folders']['uploads'].'footernewsletter.png';
        $logo = $yml['parameters']['folders']['uploads'].'logo-actualidad-light.png';
        $j1 = 0; // Contador de correos exitosos 1 mes
        $j2 = 0; // Contador de correos exitosos 15 dias
        $j3 = 0; // Contador de correos exitosos 2 dias
       
        foreach ($r as $re)
        {

            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($re['usuario_id']);
            $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($re['pagina_id']);

            if ($j1 == 100)
            {
                // Cantidad tope de correos en una corrida
                break;
            }

            if ($usuario->getCorreo() != '')
            {
                // Validar que no se haya enviado el correo a este destinatario
                $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:AdminCorreo c        
                                           WHERE c.tipoCorreo =:tipoCorreo
                                           AND c.entidadId =:entidadId
                                           AND c.usuario =:usuario_id
                                           AND c.correo =:correo
                                           AND c.fecha BETWEEN :fecha_inicio AND :fecha_fin ");

                $query->setParameters(array('tipoCorreo' => $yml2['parameters']['tipo_correo']['aviso_vecimiento'],
                                            'entidadId' => $re['pagina_id'],
                                            'usuario_id' => $usuario->getId(),
                                            'correo' => $usuario->getCorreo(),
                                            'fecha_inicio' => $fecha_inicio,
                                            'fecha_fin' => $fecha_fin));
                $correo_bd = $query->getResult();

                if (!$correo_bd)
                {

                    $mensaje1 ='<p><span style="color:#808080">'.$translator->trans('El próximo').' '.$fecha->format('d').' '.$translator->trans('de').' '.$f->mesTraducido($fecha->format('n')).' '.$translator->trans('se vencerá el acceso al libro').' '.$pagina->getTitulo().' '.$translator->trans('de').' '.strtolower($pagina->getGrado()->getDescripcion()).'. '.$translator->trans('Todavía estás a tiempo de ingresar a la plataforma y disfrutar de nuestro contenido').'.</span></p>
                                <p><span style="color:#808080">'.$translator->trans('Ingresa a la dirección web').' www.actualidaddigital.com.do, '.$translator->trans('coloca tus datos de acceso y diviértete aprendiendo con nuestros recursos').'.</span></p>';
                    $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                               'datos' => array('nombre' => $usuario->getNombre(),
                                                                'apellido' => $usuario->getApellido(),
                                                                'mensaje' => $mensaje1,
                                                                'footer' => $footer,
                                                                'logo' => $logo,
                                                                'titulo' => $titulo,
                                                                'url_plataforma' => $base),
                                               'asunto' => $translator->trans('Últimos 30 días de acceso a Actualidad Digital'),
                                               'remitente' => $yml['parameters']['mailer_user_info'],
                                               'remitente_name' => $yml['parameters']['mailer_user_info_name'],
                                               'destinatario' => $usuario->getCorreo(),
                                               'mailer' => 'info_mailer');
                    $ok = $f->sendEmail($parametros_correo);
                    
                    if ($ok)
                    {

                        $j1++;

                        $output->writeln($j1.' .----------------------------------------------------------------------------------------------');
                        $output->writeln('usuario_id: '.$usuario->getId());
                        $output->writeln('Usuario: '.$usuario->getNombre().' '.$usuario->getApellido());
                        $output->writeln('Correo enviado a '.$usuario->getCorreo());

                        // Registro del correo recien enviado
                        $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['aviso_vecimiento']);
                        $email = new AdminCorreo();
                        $email->setTipoCorreo($tipo_correo);
                        $email->setEntidadId($re['id']);
                        $email->setUsuario($usuario);
                        $email->setCorreo($usuario->getCorreo());
                        $email->setFecha(new \DateTime('now'));
                        $em->persist($email);
                        $em->flush();

                        $alarma = new AdminAlarma();
                        $alarma->setTipoAlarma($tipo_alarma);
                        $alarma->setDescripcion($translator->trans('Libro próximo a vencerse en 1 mes').': '.$pagina->getTitulo());
                        $alarma->setUsuario($usuario);
                        $alarma->setEntidadId($re['id']);
                        $alarma->setLeido(false);
                        $alarma->setFechaCreacion(new \DateTime('now'));
                        $em->persist($alarma);
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
        
        // Llamada a la función que verifica aquellos libros que le falta 15 dias para vencerse
        $hoy = date('Y-m-d');
        $fecha = new \DateTime();
        $fecha->modify('+15 days');
        $fecha_v = $fecha->format('Y-m-d');

        $query = $em->getConnection()->prepare('SELECT
                                                    fnaviso_vencimiento(:re, :pfecha) as
                                                    resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pfecha', $fecha_v, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re)
        {

            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($re['usuario_id']);
            $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($re['pagina_id']);

            if ($j2 == 100)
            {
                // Cantidad tope de correos en una corrida
                break;
            }

            if ($usuario->getCorreo() != '')
            {

                // Validar que no se haya enviado el correo a este destinatario
                $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:AdminCorreo c        
                                           WHERE c.tipoCorreo =:tipoCorreo
                                           AND c.entidadId =:entidadId
                                           AND c.usuario =:usuario_id
                                           AND c.correo =:correo
                                           AND c.fecha BETWEEN :fecha_inicio AND :fecha_fin ");

                $query->setParameters(array('tipoCorreo' => $yml2['parameters']['tipo_correo']['aviso_vecimiento'],
                                            'entidadId' => $re['id'],
                                            'usuario_id' => $usuario->getId(),
                                            'correo' => $usuario->getCorreo(),
                                            'fecha_inicio' => $fecha_inicio,
                                            'fecha_fin' => $fecha_fin));
                $correo_bd = $query->getResult();

                if (!$correo_bd)
                {

                    $mensaje2 ='<p><span style="color:#808080">'.$translator->trans('El próximo').' '.$fecha->format('d').' '.$translator->trans('de').' '.$f->mesTraducido($fecha->format('n')).' '.$translator->trans('se vencerá el acceso al libro').' '.$pagina->getTitulo().' '.$translator->trans('de').' '.strtolower($pagina->getGrado()->getDescripcion()).'. '.$translator->trans('Todavía estás a tiempo de ingresar a la plataforma y disfrutar de nuestro contenido').'.</span></p>
                                <p><span style="color:#808080">'.$translator->trans('Ingresa a la dirección web').' www.actualidaddigital.com.do, '.$translator->trans('coloca tus datos de acceso y diviértete aprendiendo con nuestros recursos').'.</span></p>';

                    $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                               'datos' => array('nombre' => $usuario->getNombre(),
                                                                'apellido' => $usuario->getApellido(),
                                                                'mensaje' => $mensaje2,
                                                                'footer' => $footer,
                                                                'logo' => $logo,
                                                                'titulo' => $titulo,
                                                                'url_plataforma' => $base),
                                               'asunto' => $translator->trans('Últimos 15 días de acceso a Actualidad Digital'),
                                               'remitente' => $yml['parameters']['mailer_user_info'],
                                               'remitente_name' => $yml['parameters']['mailer_user_info_name'],
                                               'destinatario' => $usuario->getCorreo(),
                                               'mailer' => 'info_mailer');
                    $ok = $f->sendEmail($parametros_correo);
                    
                    if ($ok)
                    {

                        $j2++;

                        $output->writeln($j2.' .----------------------------------------------------------------------------------------------');
                        $output->writeln('usuario_id: '.$usuario->getId());
                        $output->writeln('Usuario: '.$usuario->getNombre().' '.$usuario->getApellido());
                        $output->writeln('Correo enviado a '.$usuario->getCorreo());

                        // Registro del correo recien enviado
                        $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['aviso_vecimiento']);
                        $email = new AdminCorreo();
                        $email->setTipoCorreo($tipo_correo);
                        $email->setEntidadId($re['id']);
                        $email->setUsuario($usuario);
                        $email->setCorreo($usuario->getCorreo());
                        $email->setFecha(new \DateTime('now'));
                        $em->persist($email);
                        $em->flush();

                        $alarma = new AdminAlarma();
                        $alarma->setTipoAlarma($tipo_alarma);
                        $alarma->setDescripcion($translator->trans('Libro próximo a vencerse en 15 días').': '.$pagina->getTitulo());
                        $alarma->setUsuario($usuario);
                        $alarma->setEntidadId($re['id']);
                        $alarma->setLeido(false);
                        $alarma->setFechaCreacion(new \DateTime('now'));
                        $em->persist($alarma);
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

        // Llamada a la función que verifica aquellos libros que le falta 2 dias para vencerse
        $hoy = date('Y-m-d');
        $fecha = new \DateTime();
        $fecha->modify('+2 days');
        $fecha_v = $fecha->format('Y-m-d');

        $query = $em->getConnection()->prepare('SELECT
                                                    fnaviso_vencimiento(:re, :pfecha) as
                                                    resultado; fetch all from re;');
        $re = 're';
        $query->bindValue(':re', $re, \PDO::PARAM_STR);
        $query->bindValue(':pfecha', $fecha_v, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        foreach ($r as $re)
        {

            $usuario = $em->getRepository('ActualidadComunBundle:AdminUsuario')->find($re['usuario_id']);
            $pagina = $em->getRepository('ActualidadComunBundle:EaPagina')->find($re['pagina_id']);

            if ($j3 == 100)
            {
                // Cantidad tope de correos en una corrida
                break;
            }

            if ($usuario->getCorreo() != '')
            {

                // Validar que no se haya enviado el correo a este destinatario
                $query = $em->createQuery("SELECT c FROM ActualidadComunBundle:AdminCorreo c        
                                           WHERE c.tipoCorreo =:tipoCorreo
                                           AND c.entidadId =:entidadId
                                           AND c.usuario =:usuario_id
                                           AND c.correo =:correo
                                           AND c.fecha BETWEEN :fecha_inicio AND :fecha_fin ");

                $query->setParameters(array('tipoCorreo' => $yml2['parameters']['tipo_correo']['aviso_vecimiento'],
                                            'entidadId' => $re['id'],
                                            'usuario_id' => $usuario->getId(),
                                            'correo' => $usuario->getCorreo(),
                                            'fecha_inicio' => $fecha_inicio,
                                            'fecha_fin' => $fecha_fin));
                $correo_bd = $query->getResult();

                if (!$correo_bd)
                {

                    $mensaje3 ='<p><span style="color:#808080">'.$translator->trans('Te recordamos que tu acceso al libro').' '.$pagina->getTitulo().' '.$translator->trans('de').' '.strtolower($pagina->getGrado()->getDescripcion()).'. '.$translator->trans('Podrás acceder hasta el día').' '.$fecha->format('d').' '.$translator->trans('de').' '.$f->mesTraducido($fecha->format('n')).'.</span></p>
                                <p><span style="color:#808080">'.$translator->trans('Ingresa a la dirección web').' www.actualidaddigital.com.do, '.$translator->trans('coloca tus datos de acceso y diviértete aprendiendo con nuestros recursos').'.</span></p>';
                    
                                $parametros_correo = array('twig' => 'ActualidadBackendBundle:Notificacion:emailCommand.html.twig',
                                                        'datos' => array('nombre' => $usuario->getNombre(),
                                                                        'apellido' => $usuario->getApellido(),
                                                                        'mensaje' => $mensaje3,
                                                                        'footer' => $footer,
                                                                        'logo' => $logo,
                                                                        'titulo' => $titulo,
                                                                        'url_plataforma' => $base),
                                                        'asunto' => $translator->trans('Libro por expirar'),
                                                        'remitente' => $yml['parameters']['mailer_user_info'],
                                                        'remitente_name' => $yml['parameters']['mailer_user_info_name'],
                                                        'destinatario' => $usuario->getCorreo(),
                                                        'mailer' => 'info_mailer');
                            $ok = $f->sendEmail($parametros_correo);
                    
                    if ($ok)
                    {

                        $j3++;

                        $output->writeln($j3.' .----------------------------------------------------------------------------------------------');
                        $output->writeln('usuario_id: '.$usuario->getId());
                        $output->writeln('Usuario: '.$usuario->getNombre().' '.$usuario->getApellido());
                        $output->writeln('Correo enviado a '.$usuario->getCorreo());

                        // Registro del correo recien enviado
                        $tipo_correo = $em->getRepository('ActualidadComunBundle:AdminTipoCorreo')->find($yml2['parameters']['tipo_correo']['aviso_vecimiento']);
                        $email = new AdminCorreo();
                        $email->setTipoCorreo($tipo_correo);
                        $email->setEntidadId($re['id']);
                        $email->setUsuario($usuario);
                        $email->setCorreo($usuario->getCorreo());
                        $email->setFecha(new \DateTime('now'));
                        $em->persist($email);
                        $em->flush();

                        $alarma = new AdminAlarma();
                        $alarma->setTipoAlarma($tipo_alarma);
                        $alarma->setDescripcion($translator->trans('Libro próximo a vencerse en 2 días').': '.$pagina->getTitulo());
                        $alarma->setUsuario($usuario);
                        $alarma->setEntidadId($re['id']);
                        $alarma->setLeido(false);
                        $alarma->setFechaCreacion(new \DateTime('now'));
                        $em->persist($alarma);
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

        $output->writeln('1 mes para vencimiento '.$j1);
        $output->writeln('15 días para vencimiento '.$j2);
        $output->writeln('2 días para vencimiento '.$j3);
        
    }
}