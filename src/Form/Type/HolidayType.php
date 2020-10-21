<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HolidayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $countryChoiceArr = [];
        
        foreach ($options['countries'] as $country) {
            if (array_key_exists('fullName', $country) && array_key_exists('countryCode', $country)) {
                $countryChoiceArr[$country['fullName']] = $country['countryCode'];
            }
        }

        $builder
            ->add('year', NumberType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('country', ChoiceType::class, [
                'choices'  => $countryChoiceArr,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Check Holidays',
                'attr' => ['class' => 'form-control mt-2'],
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'countries' => [],
        ]);

        $resolver->setAllowedTypes('countries', 'array');
    }
}

