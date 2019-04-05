<?php

namespace App\Form;

use App\Entity\Production;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
//            ->add('categories', CollectionType::class, array(
//                'entry_type' => CategoryType::class,
//                'allow_add' => true,
//                'allow_delete' => true
//            ))
            ->add('pictures', CollectionType::class, array(
                'entry_type' => PictureType::class,
                'allow_add' => true,
                'allow_delete' => true
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Production::class,
            'csrf_protection' => false,
        ]);
    }
}
