<?php

namespace App\Form;

use App\Entity\Expense;
use App\Entity\User;
use App\Entity\Project;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $project = $options['project'];

        $builder
            ->add('title')
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
            ])
            ->add('date', DateType::class,[
                'widget' => 'single_text',
            ])
           
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choices' => $project->getUser(),
                'label' => 'Utilisateur'
            ])
           
            
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('id');
        $resolver->setDefaults([
            'data_class' => Expense::class,
            'project' => null
        ]);
    }
}
