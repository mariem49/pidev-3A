<?php

namespace App\Form;

use App\Entity\Cour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Cours',
                'attr' => ['placeholder' => 'Entrez le nom du cours'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du Cours',
                'attr' => ['placeholder' => 'Entrez la description du cours'],
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée du Cours (en heures)',
                'attr' => ['placeholder' => 'Entrez la durée'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cour::class,
        ]);
    }
}
