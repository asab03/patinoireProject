<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class FooterController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact( ): Response
    {
        
        return $this->render('footer/contact.html.twig',);
    }

     
    /**
     * @Route("/mentions-legales", name="mentions")
     */
    public function mentions( ): Response
    {
        
        return $this->render('footer/mentions.html.twig',);
    }

    
    
}