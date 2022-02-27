<?php

namespace App\Controller;

use App\Entity\Discussion;
use App\Entity\Project;
use App\Entity\ChatMessage;
use App\Repository\ChatMessageRepository;
use App\Repository\DiscussionRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\DiscussionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\WebLink\Link;


class DiscussionController extends AbstractController
{
    /**
     * @Route("/project/{id}/discussion/{disc_id}", methods={"GET"}, name="project_discussion")
     * @ParamConverter("discussion", options={"id" = "disc_id"})
     */
    public function projectsDiscussion(DiscussionRepository $discussionRepository, Project $project, ChatMessageRepository $chatMessageRepository, Request $request,Discussion $discussion): Response
    {
        $discussions = $discussionRepository->findAll();
        

        $projectDiscussion = $project->getDiscussion();
       
        $chatMessages= $chatMessageRepository-> findBy([
            'discussion'=> $discussion,       
        ]);
        
        //$hubUrl = $this->getParameter('mercure.default_hub'); // Mercure automatically define this parameter
       //$this->addLink($request, new Link('mercure', $hubUrl)); // Use the WebLink Component to add this header to the following response

        return $this->render('discussion/discussion.html.twig', [
            'project' => $project,
            'discussion' => $discussions[0],
            'projectDiscussion' => $projectDiscussion,
            'id'=> $project->getId(),
            'disc_id'=> $discussion->getId() ,
            'chatMessage'=> $chatMessages,
            
            
        ]);
    }
   

   /**
     * @Route("/project/{id}/discussion/{disc_id}/refresh", methods={"GET"}, name="project_discussion_refresh")
     * @ParamConverter("discussion", options={"id" = "disc_id"})
     */
    public function projectsDiscussionRefresh(DiscussionRepository $discussionRepository, Project $project, SerializerInterface $serializer,ChatMessageRepository $chatMessageRepository, Discussion $discussion): JsonResponse
    {
        $discussions = $discussionRepository->findAll();
        

        $projectDiscussion = $project->getDiscussion();
       
        $chatMessages= $chatMessageRepository-> findBy([
            'discussion'=> $discussion,       
        ]);
        
        $jsonMessage = $serializer->serialize($chatMessages, ChatMessage::class,'json', [
            'groups' => ['chatMessage'] // On serialize la réponse avant de la renvoyer
        ]);
        

        return new JsonResponse( // Enfin, on retourne la réponse
            $jsonMessage,
            Response::HTTP_OK,
            [],
            
        );
            
        
    }

    /**
     * @Route("/project/{id}/discussion/{disc_id}", name="project_discussionMessage")
     * @ParamConverter("discussion", options={"id" = "disc_id"})
     * 
     */
    public function DiscussionGetMessage(DiscussionRepository $discussionRepository, Project $project, ChatMessageRepository $chatMessageRepository, Discussion $discussion): Response
    {
        
        
        // $chatMessage = $discussion->getChatMessages();

        return $this->render('discussion/discussion.html.twig', [
            
            'project' => $project,
            'discussion' => $discussion,
            
        ]);
    }
    /**
     * @Route("/message", name="message", methods={"POST"})
     */
    public function sendMessage(Request $request, DiscussionRepository $discussionRepository, SerializerInterface $serializer, EntityManagerInterface $em, HubInterface $hub): JsonResponse
    {
        $data = \json_decode($request->getContent(), true); // On récupère les data postées et on les déserialize
        if (empty($content = $data['content'])) {
            throw new AccessDeniedHttpException('No data sent');
        }

        $discussion = $discussionRepository->findOneBy([
            'id' => $data['discussion'] // On cherche à savoir de quelle discussion provient le message
        ]);
        if (!$discussion) {
            throw new AccessDeniedHttpException('Message have to be sent on a specific discussion');
        }
        
        $chatMessage = new ChatMessage(); // Après validation, on crée le nouveau message
        $chatMessage->setMessageContent($content);
        $chatMessage->setDiscussion($discussion);
        $chatMessage->setUser($this->getUser()); // On lui attribue comme auteur l'utilisateur courant
        $chatMessage->setSendingDate(new \DateTime('now'));

        $em->persist($chatMessage);
        $em->flush(); // Sauvegarde du nouvel objet en DB

        $jsonMessage = $serializer->serialize($chatMessage, 'json', [
            'groups' => ['chatMessage'] // On serialize la réponse avant de la renvoyer
        ]);

       
        return new JsonResponse( // Enfin, on retourne la réponse
            $jsonMessage,
            Response::HTTP_OK,
            [],
            
        );
    }
    
}
