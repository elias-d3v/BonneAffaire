<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Service\DepartementProvider;

class PostType extends AbstractType
{
    private DepartementProvider $departementProvider;

    public function __construct(DepartementProvider $departementProvider)
    {
        $this->departementProvider = $departementProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('price')
            ->add('itemCondition', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Neuf' => 'neuf',
                    'Comme neuf' => 'comme_neuf',
                    'Bon état' => 'bon',
                    'Usagé' => 'usage',
                    'Pour pièces' => 'pieces',
                ],
                'placeholder' => 'Sélectionner un état',
            ])
            ->add('location')
            ->add('postalCode', ChoiceType::class, [
            'label' => 'Département',
            'choices' => array_flip($this->departementProvider->getDepartements()),
            'placeholder' => 'Sélectionnez un département',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionner une catégorie',
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
