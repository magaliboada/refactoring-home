<?php

namespace ScraperCommand;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ScraperCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        // $this
        //     ->setName('vabadus:blog:comentarios')
        //     ->setDescription('Envía por correo electrónico comentarios sin publicar')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // $em = $this->getContainer()->get('doctrine')->getManager();

        // $comentarios = $em->getRepository('BlogBundle:Comentario')
        //     ->findBy(array('publicado' => 0));
        // $texto_mail = "";
        // foreach ($comentarios as $comentario) {
        //     $texto_mail .= $comentario->getTitle()."\r\n";
        // }        

        // $mensaje = \Swift_Message::newInstance()
        //     ->setSubject("Comentarios despublicados")
        //     ->setFrom("hola@vabadus.es")
        //     ->setTo("admin@vabadus.es")
        //     ->setBody($texto_mail);
        
        // $this->getContainer()->get('mailer')->send($mensaje);
    }
}
