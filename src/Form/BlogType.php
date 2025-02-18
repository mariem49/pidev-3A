<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Blog Title'
            ])
            ->add('description', TextType::class, [
                'label' => 'Description'
            ])
            ->add('createdAtBlog', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Creation Date'
            ])
            ->add('updatedAtBlog', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Update Date',
                'required' => false
            ])
            ->add('_token', HiddenType::class, [  // Ajout du champ CSRF explicitement
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
            'csrf_protection' => true,   // Activation de la protection CSRF
            'csrf_field_name' => '_token',  // Nom du champ CSRF
            'csrf_token_id'   => 'blog',  // Identifiant du token
        ]);
    }
}
