<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Image; // ✅ Correct import for the Image constraint
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your name.']),
                ],
            ])
            ->add('lastname', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Please enter your last name.']),
                ],
            ])
            ->add('email', null, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Email cannot be empty.']),
                    new Assert\Email(['message' => 'The email should be in a valid format.']),
                ],
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Enter password',
                    'class' => 'password-field',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Password cannot be empty.']),
                    new Assert\Length([
                        'min' => 8,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*\d).{8,}$/',
                        'message' => 'Password must contain at least one uppercase letter, one number, and be at least 8 characters long.',
                    ]),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    
                    'Teacher' => 'TEACHER',
                    'Student' => 'STUDENT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'required' => true,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('profilepic', FileType::class, [
                'label' => 'Profile Picture (JPEG/PNG, max 2MB)',
                'required' => false, // Passer à `true` si l'image est obligatoire
                'mapped' => false, // Empêche Symfony de vouloir stocker un fichier dans l'entité User
                'attr' => ['accept' => 'image/jpeg,image/png'],
                'constraints' => [
                    new Assert\Image([
                        'maxSize' => '2M',
                        'maxSizeMessage' => 'The image is too large. Maximum size is {{ limit }}.',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG or PNG).',
                    ]),
                ],
            
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
