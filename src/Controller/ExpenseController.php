<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Expense;
use App\Repository\ExpenseRepository;
use App\Entity\Project;
use App\Form\ExpenseType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ExpenseController extends AbstractController
{
    /**
     * @Route("/project/{id}/expense", methods={"GET"}, name="projects_expense")
     */
    public function projectsExpenses(ExpenseRepository $expenseRepository, Project $project): Response
    {
        $expense = $expenseRepository->findAll();

        $projectexpenses = $project->getExpenses();

        return $this->render('project/expenses.html.twig', [
            'project' => $project,
            'expenses' => $expense,
            'projectExpenses' => $projectexpenses,
        ]);
    }
    
    /**
     * @Route("/project/{id}/add_expense", name="projects_addExpense")
     */
    public function projectsAddExpense(Project $project, Request $request, SluggerInterface $slugger,ValidatorInterface $validator,ManagerRegistry $doctrine, ExpenseRepository $expenseRepository, EntityManagerInterface $entityManager): Response
    {
        $id=$request->query->get('id');
        $expense = new Expense();
        $form = $this->createForm(ExpenseType::class, $expense, ['id' => $id, 'project' => $project]);
        $form->handleRequest($request);
        $user = $project-> getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            //$format = 'Y-m-d';
            
            $expense->setTitle($form->get('title')->getData());
            $expense->setAmount($form->get('amount')->getData());
            // $expense->setDate(\DateTime::createFromFormat($form->get('amount')->getData(), $format));
            $expense->setDate($form->get('date')->getData());
            $expense->setUser( $form->get('user')->getData());
            $expense->setProject($project);
           
            
            $entityManager->persist($expense);
            $entityManager->flush();
            

            // ... persist the $product variable or any other work

            return $this->redirectToRoute('projects_expense',[
                'id'=> $project->getId()
            ]);
        }
        return $this->renderForm('expense/addExpense.html.twig', [
            'form' => $form,
            'project'=> $project,
            'id'=> $project->getId(),
            'user' => $user

            
            
        ]);
    }
     /**
     * @Route("/project/{id}/expense/{expense_id}/edit", methods={"GET","POST"}, name="expense_edit")
     * @ParamConverter("expense", options={"id" = "expense_id"})
     * 
     */
    public function editExpense(Expense $expense, Project $project): Response
    {
        $users = $project->getUser();
        
        return $this->render('expense/editexpense.html.twig',[
            'projectId' => $project->getId(),
            'expenseId' => $expense->getId(),
            'expense' => $expense,
            'project' => $project,
            'user' => $users
            
        ]) ;
    }
    /**
     * @Route("/project/{id}/expense/{expense_id}/save", methods={"POST"}, name="save_expense")
     * @ParamConverter("expense", options={"id" = "expense_id"})
     */
    public function saveProject(Request $request, ManagerRegistry $doctrine, Expense $expense, Project $project):Response{

        $format = 'Y-m-d';


        $expense -> setTitle($request->request->get('title'));
        $expense -> setAmount($request->request->get('amount'));
        $expense -> setDate(\DateTime::createFromFormat($format,$request->request->get('date')));
        $expense -> setUser($request->request->get('user'));
        

        
        $entityManager = $doctrine->getManager();

        $entityManager->persist($expense);
        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();
        
        return $this->redirectToRoute('projects_expense',[
            'id'=> $project->getId()
        ]);
    }
    /**
     * @Route("/project/{id}/expense/{expense_id}", name="expense_delete", methods={"GET", "DELETE"})
     * @ParamConverter("expense", options={"id" = "expense_id"})
     */
    public function delete(Request $request, Expense $expense, ManagerRegistry $doctrine, Project $project): Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($expense);
        $entityManager->flush();

        return $this->redirectToRoute('projects_expense',[
            'id'=> $project->getId()
        ]);
}
}
