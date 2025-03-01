<?php

namespace App\Form;

use App\Entity\Cour;
use App\Entity\Seance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SeanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la séance',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'La date est obligatoire.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de la séance ne peut pas être dans le passé.',
                    ]),
                ],
            ])
            ->add('professeur', TextType::class, [
                'label' => 'Professeur',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le professeur est obligatoire.',
                    ]),
                    new \Symfony\Component\Validator\Constraints\Length([
                        'min' => 4,
                        'minMessage' => 'Le nom du professeur doit contenir au moins {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('cour', EntityType::class, [
                'class' => Cour::class,
                'choice_label' => 'nom',
                'label' => 'Cours',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'Le cours est obligatoire.',
                    ]),
                ],
            ])
           ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seance::class,
        ]);
    }
}
