<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use FrontBundle\Form\ActorType;
use Doctrine\ORM\EntityRepository;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('actor', new ActorType());
        
        $builder->add('city', 'text');
        $builder->add('state', 'entity', array(
                'class' => 'CoreBundle:State',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
                },
                'required' => true,
                'placeholder' => 'Selecciona una provincia',
                'empty_data'  => true,
            ));
        $builder->add('country', 'entity', array(
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
            'checkbox',
            array('property_path' => 'termsAccepted','label' => 'Accept all terms')
        );
    }

}
