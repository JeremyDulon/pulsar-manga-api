<?php

namespace App\Form;

use App\Entity\ComicLanguage;
use App\Utils\PlatformUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ComicLanguageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'EN' => PlatformUtil::LANGUAGE_EN,
                    'FR' => PlatformUtil::LANGUAGE_FR
                ]
            ])
            ->add('description')
            ->add('autoUpdate')
            ->add('comicPlatforms', CollectionType::class, [
                'entry_type' => ComicPlatformType::class,
                'allow_add' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ComicLanguage::class,
        ]);
    }
}
