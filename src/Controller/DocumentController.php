<?php

namespace App\Controller;

use App\Entity\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DocumentRepository;
use App\Entity\Project;
use App\Form\DocumentType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class DocumentController extends AbstractController
{
    /**
     * @Route("/document", name="document")
     */
    public function index(DocumentRepository $documentRepository, Document $document  ): Response
    {
        $documents = $documentRepository->findAll();
        $project = $document -> getProject();
        return $this->render('document/index.html.twig', [
            'documents' => $documents,
            'project' => $project,
            'projectId' => $project->getId(),
        ]);
    }

     
    /**
     * @Route("/project/{id}/document/{document_id}", name="document_delete", methods={"GET", "DELETE"})
     * @ParamConverter("document", options={"id" = "document_id"})
     */
    public function deleteDocument(Request $request, Document $document, ManagerRegistry $doctrine, Project $project): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($document);
        $entityManager->flush();

        return $this->redirectToRoute('projects_document',[
            'id'=> $project->getId()
        ]);
}

    
    
}