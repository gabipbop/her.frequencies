<?php

namespace App\Form\ArtistRequest;

use App\Entity\ArtistRequest;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class Step2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('rawCategories', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'multiple' => true,
            'expanded' => true,
            'mapped' => false,
            'label' => 'Rôles',
        ]);

        // On convertit les entités Category en tableau d'IDs pour le JSON
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $artistRequest = $event->getData();

            $categories = $form->get('rawCategories')->getData();
            $ids = array_map(fn(Category $c) => $c->getId(), $categories->toArray());
            $artistRequest->setRawCategories($ids);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArtistRequest::class]);
    }
}
