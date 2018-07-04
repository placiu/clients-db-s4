<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditClient extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nip', TextType::class, ['label' => false, 'attr' => ['class' => 'form-control']])
            ->add('regon', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
            ->add('name', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
            ->add('city', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
            ->add('street', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
            ->add('zipCode', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
            ->add('province', TextType::class, ['required' => false, 'label' => false, 'attr' => ['class' => 'form-control']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
