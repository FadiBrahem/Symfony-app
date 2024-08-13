<?php

namespace App\Form;

use App\Entity\Candidat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password')
            ->add('phone')
            ->add('address')
            ->add('town')
            ->add('fb')
            ->add('linkdin')
            ->add('description')
            ->add('img')
            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance')
            ->add('nivEtude')
            ->add('typeCandidat')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
        ]);
    }
}
