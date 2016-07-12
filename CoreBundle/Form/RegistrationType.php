<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use CoreBundle\Form\ActorRegisterType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('actor', ActorRegisterType::class);
        $builder->add('city');
        $builder->add('state', EntityType::class, array(
                'class' => 'CoreBundle:State',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'required' => true,
                'placeholder' => 'Selecciona una provincia',
                'empty_data'  => true,
            ));
        $builder->add('country', EntityType::class, array(
                'class' => 'CoreBundle:Country',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'required' => true,
                'placeholder' => 'Selecciona un país',
                'empty_data'  => true,
            ));
                
        $builder->add(
            'terms',
             CheckboxType::class,
            array('property_path' => 'termsAccepted','label' => 'Accept all terms')
        );
    }

}
