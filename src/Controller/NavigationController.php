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
use App\Form\AdminEmail;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

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
           
           
            
            //if($users && in_array('ROLE_USER', $users->getRoles())){
            return $this->render('navigation/home.html.twig',[
                
                
                
            ]);

            // $session->set("message", "Vous devez etre connecté pour acceder, vous avez été redirigé sur cette page");
            // return $this->redirectToRoute('connection');
        

        }


    /**
    * @Route("/admin", name="index")
    * 
    */
        public function admin(SessionInterface $session,UserRepository $userRepository, ProjectRepository $projectRepository):Response
        {
            $user = $this->getUser();
            
            if($user && in_array('ROLE_ADMIN', $user->getRoles())){
                return $this->render('user/index.html.twig', ['users'=> $userRepository-> findAll(), 'projects' => $projectRepository-> findAll()]);
        }

        $session->set("message", "Vous n'avez pas le droit d'acceder à la page admin, vous avez été redirigé sur cette page");
        return $this->redirectToRoute('home');
        }

    
    /**
    * @Route("/admin/email/send", name="admin_mail_send",  methods={"GET", "POST"})
    * 
    */
    public function adminSendMail(SessionInterface $session,UserRepository $userRepository,Request $request, ManagerRegistry $doctrine, MailerInterface $mailer):Response
    {
        $form = $this->createForm(AdminEmail::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $donnees = $form ->getData();

            $subject = $donnees['subject'];
            $body = $donnees['body'];

            
            // On génère l'e-mail
            $message =   (new Email())
            ->from(new Address('patinoiredev7@gmail.com', 'Patinoire mail Bot'))
            ->to($donnees['email'])
            ->subject($subject)
            ->html( $body)
            ;

            // On envoie l'e-mail
            $mailer->send($message);

            // On crée le message flash de confirmation
            $this->addFlash('message', "E-mail à l'utilisateur envoyé !");

            return $this->redirectToRoute('index');
                  
            }

        return $this->render('user/adminEmail.html.twig',[
                'form' => $form-> createView(),
                

            ]) ;

    }
}
