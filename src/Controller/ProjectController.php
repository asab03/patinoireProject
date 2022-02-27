<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Document;
use App\Form\DocumentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Project;
use App\Repository\DiscussionRepository;
use App\Repository\DocumentRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectController extends AbstractController
{
    /**
     * @Route("/projects", name="projects")
     */
    public function project(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * @Route("/project/add", methods={"GET","POST"}, name="project_add")
     * 
     */
    public function addProject(ProjectRepository $projectRepository,LoggerInterface $logger, SessionInterface $session): Response
    {
       

        return $this->render('project/addproject.html.twig') ;
    }
   /**
     * @Route("/project/add/save", methods={"POST"}, name="project_add_save")
     */
    public function projectsAddSave(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator, ProjectRepository $projectRepository, DiscussionRepository $discussionRepository): Response
    {
        $entityManager = $doctrine->getManager();

        $format = 'Y-m-d';

        $project = new Project();
        $project->setTitle($request->request->get('title'));
        $project->setDescription($request->request->get('description'));
        $project->setStartDateStr($request->request->get('date_in'));
        $project->setEndDateStr($request->request->get('date_out'));
        $project->addUser($this->container->get('security.token_storage')->getToken()->getUser());         

        $errors = $validator->validate($project);

        if (count($errors) > 0) {

            //$errorsString = (string) $errors;

            $this-> addFlash('errors',$errors);

            return $this-> redirectToRoute('project_add'); 
        }

        $project->setDateIn(\DateTime::createFromFormat($format, $request->request->get('date_in')));
        $project->setDateOut(\DateTime::createFromFormat($format, $request->request->get('date_out')));
    
        $entityManager->persist($project);
        $entityManager->flush();

        $discussion= new Discussion;
        $discussion->setProject($project);
    
        $entityManager->persist($discussion);
        $entityManager->flush();

        return $this->redirectToRoute('home');
    }
    /**
     * @Route("project/{id}", name="project_show", methods={"GET"})
     */
    public function show(Project $project,UserRepository $userRepository): Response
    {
        $discussion = $project-> getDiscussion();

        return $this->render('project/showproject.html.twig', [
            'project' => $project,
            'discussion'=> $discussion
        ]);
    }
    /**
     * @Route("/project/edit/{id}", methods={"GET","POST"}, name="project_edit")
     * 
     */
    public function editProject(ProjectRepository $projectRepository, Project $project): Response
    {
        $users = $project->getUser();
        
        return $this->render('project/editproject.html.twig',[
            'projectId' => $project->getId(),
            'project' => $project,
            'users' => $users,
        ]) ;
    }

     /**
     * @Route("/project/save/{id}", methods={"POST"}, name="save_project")
     */
    public function saveProject(Request $request, ManagerRegistry $doctrine, Project $project):Response{

        $format = 'Y-m-d';


        $project -> setTitle($request->request->get('title'));
        $project -> setDescription($request->request->get('description'));
        $project -> setDateIn(\DateTime::createFromFormat($format,$request->request->get('date_in')));
        $project -> setDateOut(\DateTime::createFromFormat($format,$request->request->get('date_out')));
        

        
        $entityManager = $doctrine->getManager();

        $entityManager->persist($project);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        
        return $this->redirectToRoute('project_show',[
            'id'=> $project->getId()
        ]);
    }

     /**
     * @Route("/project/delete/{id}", methods={"GET", "DELETE"}, name="project_delete")
     * 
     */
    public function deleteProject(Request $request, Project $project, ManagerRegistry $doctrine)
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($project);
        
        $entityManager->flush();

        return $this->redirectToRoute('projects');
    }
    
    /**
     * @Route("/project/{id}/user", methods={"GET"}, name="projects_addUser")
     */
    public function projectsAddUser(UserRepository $userRepository, Project $project): Response
    {
        $users = $userRepository->findAll();

        $projectUsers = $project->getUser();

        return $this->render('project/addUser.html.twig', [
            'project' => $project,
            'users' => $users,
            'projectUsers' => $projectUsers,
        ]);
    }

    /**
     * @Route("/project/{id}/user/save", methods={"POST"}, name="projects_addUser_save")
     */
    public function projectsAddUserSave(LoggerInterface $logger, ManagerRegistry $doctrine, UserRepository $userRepository, Request $request, Project $project): Response
    {
        $entityManager = $doctrine->getManager();

        $project->clearUsers();

        $listIdUser = $request->request->get('user_id', []);

        $logger->debug("valeur userId", ["userId"=>$listIdUser]);
        
        foreach($listIdUser as $userId) {
            $user = $userRepository->find($userId);
            $project->addUser($user);
        }

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->redirectToRoute('project_show',[
            'id'=> $project->getId()
        ]);
    }
    /**
     * @Route("/project/{id}/document", methods={"GET"}, name="projects_document")
     */
    public function projectsDocument(DocumentRepository $documentRepository, Project $project): Response
    {
        $document = $documentRepository->findAll();

        $projectdocument = $project->getDocuments();

        return $this->render('project/document.html.twig', [
            'project' => $project,
            'documents' => $document,
            'projectDocuments' => $projectdocument,
        ]);
    }
    /**
     * @Route("/project/{id}/add_document",  name="projects_addDocument")
     */
    public function projectsAddDocument( Project $project, Request $request, SluggerInterface $slugger,ValidatorInterface $validator,ManagerRegistry $doctrine, DocumentRepository $documentRepository, EntityManagerInterface $entityManager): Response
    {
            
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        
               
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $format = 'Y-m-d';
            /** @var UploadedFile $document */
            
            $documentFile = $form->get('document')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($documentFile) {
                $originalFilename = pathinfo($documentFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$documentFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $documentFile->move(
                        $this->getParameter('document_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $document->setDocument($newFilename);
            }
            $document->setTitle($form->get('title')->getData());
            
            $document->setDate( $form->get('date')->getData());
            $document->setProject($project);
            
            $entityManager->persist($document);
            $entityManager->flush();

            // ... persist the $product variable or any other work

            return $this->redirectToRoute('projects_document',[
                'id'=> $project->getId()
            ]);
        }
        return $this->renderForm('project/adddocument.html.twig', [
            'form' => $form,
            'project'=> $project
        ]);
    }
    /**
     * @Route("project/{id}/document/{doc_id}/show", name="project_document_show", methods={"GET"})
     * @ParamConverter("document", options={"id" = "doc_id"})
     */
    public function documentShow(DocumentRepository $documentRepository, Project $project,Document $document): Response
    {
        
        $projectRoot = $this->getParameter('kernel.project_dir');
        $filename = $document-> getDocument();
        return $this->file($projectRoot.'/public/uploads/documents/'.$filename, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }


}
