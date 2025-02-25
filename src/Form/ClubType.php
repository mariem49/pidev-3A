<?php

namespace App\Form;

use App\Entity\Club;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class ClubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du club est obligatoire.',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 50,
                        'minMessage' => 'Le nom du club doit contenir au moins 10 caractères.',
                        'maxMessage' => 'Le nom du club ne peut pas dépasser 50 caractères.',
                    ]),
                ],
            ])
            ->add('logo', FileType::class, [
                'label' => 'Image (JPG, JPEG, PNG file)',
                'mapped' => false, // This field is not mapped to an entity property
                'required' => false, // It's not required, so user can submit without selecting an image
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ]),
                ],

            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'CULTUREL' => 'CULTUREL',
                    'SPORTIF' => 'SPORTIF',
                    'EDUCATIF' => 'EDUCATIF',
                    'EVENEMENTIEL' => 'EVENEMENTIEL',
                    'AUTRE' => 'AUTRE',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le type du club est obligatoire.',
                    ]),
                ],
            ])
            ->add('date_creation', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank([
                        'message' => 'La date de création est obligatoire.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
        ]);
    }
}
