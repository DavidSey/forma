<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2,
                        'max' => 128,
                        'minMessage' => 'Votre nom doit faire au moins 2 caractères.',
                        'maxMessage' => 'Votre nom doit faire au maximum 128 caractères.'
                    ))
                )
            ))
            ->add('email', EmailType::class, array(
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2,
                        'max' => 128,
                        'minMessage' => 'Votre email doit faire au moins 2 caractères.',
                        'maxMessage' => 'Votre email doit faire au maximum 128 caractères.'
                    ))
                )
            ))
            ->add('message', TextareaType::class, array(
                'constraints' => array(
                    new Assert\Length(array(
                        'min' => 2,
                        'minMessage' => 'Votre message doit faire au moins 1 caractères.'
                    ))
                )
            ))
            ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
            'csrf_protection' => false,
        ]);
    }
}
