<?php

// src/Form/ArtistRequest/Step3Type.php
namespace App\Form\ArtistRequest;

use App\Entity\ArtistRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class Step3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pictureFile', VichImageType::class, [
            'label' => 'Photo de l\'artiste',
            'required' => false,
            'allow_delete' => false,
            'download_uri' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArtistRequest::class]);
    }
}
