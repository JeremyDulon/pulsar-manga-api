<?php

namespace App\Form;

use App\Entity\ComicPlatform;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComicPlatformType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url')
            ->add('trust', IntegerType::class, [
                'data' => 0
            ])
            ->add('status', ChoiceType::class, [
                'empty_data' => ComicPlatform::STATUS_ENABLED,
                'choices' => [
                    'Actif' => ComicPlatform::STATUS_ENABLED,
                    'En suspens' => ComicPlatform::STATUS_SUSPENDED,
                    'Inactif' => ComicPlatform::STATUS_DISABLED
                ]
            ])
            ->add('platform')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComicPlatform::class,
        ]);
    }
}
