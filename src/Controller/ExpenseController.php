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
        $expense = new Expense();
        $form = $this->createForm(ExpenseType::class, $expense);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();

            $format = 'Y-m-d';
            
            $expense->setTitle($form->get('title')->getData());
            $expense->setAmount($form->get('amount')->getData());
            // $expense->setDate(\DateTime::createFromFormat($form->get('amount')->getData(), $format));
            $expense->setDate(null);
            $expense->setUser( $form->get('user')->getData());
            $expense->setProject($project);
            $expense->addDebiteur( $form->get('debiteur')->getData());
            
            
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
            'expense'=> $expense,
        ]);
    }
}
