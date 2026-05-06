<?php

namespace App\Form\ArtistRequest;

use App\Entity\ArtistRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Step1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom de l\'artiste'])
            ->add('country', TextType::class, ['label' => 'Pays'])
            ->add('city', TextType::class, ['label' => 'Ville'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArtistRequest::class]);
    }
}
