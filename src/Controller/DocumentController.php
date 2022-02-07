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

class DocumentController extends AbstractController
{
    /**
     * @Route("/document", name="document")
     */
    public function index(DocumentRepository $documentRepository  ): Response
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
     * @Route("/project/{id}/add_document", methods={"GET"}, name="projects_addDocument")
     */
    public function AddDocument(Request $request, SluggerInterface $slugger,ValidatorInterface $validator)
    {
        $format = 'Y-m-d';
        
        $document = new Document();
        $document->setTitle($request->request->get('title'));
        $document->setDate($request->request->get('date'));
        $document->setDocument($request->request->get('document'));
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        $document->setDate(\DateTime::createFromFormat($format, $request->request->get('date_in')));
               
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $document */
            $document = $form->get('document')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($document) {
                $originalFilename = pathinfo($document->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$document->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $document->move(
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

            // ... persist the $product variable or any other work

            return $this->redirectToRoute('document');
        }
        return $this->renderForm('document/adddocument.html.twig', [
            'form' => $form,
        ]);
    }

    
    
}