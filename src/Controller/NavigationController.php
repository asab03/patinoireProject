<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Controller\UserController;
Use App\Controller\ProjectController;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NavigationController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/", name="connection")
     */
    public function connection()
    {
        return $this->render('navigation/connection.html.twig');
    }

    /**
    * @Route("/home", name="home")
    * 
    */
        public function home(SessionInterface $session, ProjectRepository $projectRepository, UserRepository $userRepository)
        {
           
           
            
            // if($users && in_array('ROLE_USER', $users->getRoles())){
            return $this->render('navigation/home.html.twig',[
                
                
                
            ]);

            // $session->set("message", "Vous devez etre connecté pour acceder, vous avez été redirigé sur cette page");
            // return $this->redirectToRoute('connection');
        

        }


    /**
    * @Route("/admin", name="index")
    * 
    */
        public function admin(SessionInterface $session,UserRepository $userRepository):Response
        {
            $user = $this->getUser();
            
            if($user && in_array('ROLE_ADMIN', $user->getRoles())){
                return $this->render('user/index.html.twig', ['users'=> $userRepository-> findAll(),]);
        }

        $session->set("message", "Vous n'avez pas le droit d'acceder à la page admin, vous avez été redirigé sur cette page");
        return $this->redirectToRoute('home');
        }   
}
