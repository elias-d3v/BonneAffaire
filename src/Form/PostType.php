<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('itemCondition')
            ->add('location')
            ->add('postalCode')
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name', // affiche le nom des catégories dans le select
            ])
            ->add('image1', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image2', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('image3', FileType::class, [
                'mapped' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
