<?php

namespace App\Form;


use App\Entity\Project;
use App\Entity\Discussion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\EntityRepository;


class DiscussionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $discussion = $options['project'];

        $builder
            
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice' => $discussion->getProject(),
            ])
           
            ->add('save', SubmitType::class)
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('id');
        $resolver->setDefaults([
            'data_class' => Discussion::class,
            'discussion' => null
        ]);
    }
}
