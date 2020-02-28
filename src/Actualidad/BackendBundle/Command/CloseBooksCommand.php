<?php

// actualidad/src/Actualidad/BackendBundle/Command/CloseBooksCommand.php

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

class CloseBooksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ea:cerrar-libros')
             ->setDescription('Setea en false el campo activo de la tabla ea_pagina_usuario cuando el libro se haya vencido');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $em = $this->getContainer()->get('doctrine')->getManager();

        // Llamada a la función que verifica aquellos libros vencidos
        $hoy = date('Y-m-d');
        $query = $em->getConnection()->prepare('SELECT
                                                fncerrar_libros(:pfecha) as
                                                resultado;');
        $query->bindValue(':pfecha', $hoy, \PDO::PARAM_STR);
        $query->execute();
        $r = $query->fetchAll();

        $output->writeln('Cantidad de libros cerradas: '.$r[0]['resultado']);
        
    }
}