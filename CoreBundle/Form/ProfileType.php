<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
        $builder->add('username', 'text');
        $builder->add('email', 'email');
        $builder->add('name', 'text');
        $builder->add('dni', 'text');
        $builder->add('address', 'text');
        $builder->add('postalCode', 'text');
        $builder->add('city', 'text');
        $builder->add('state', 'text');
        $builder->add('state', 'entity', array(
                'class' => 'CoreBundle:State',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
//                        ->where('c.parentCategory IS NOT NULL');
                },
                'required' => false
            ));
        $builder->add('country', 'entity', array(
                'class' => 'CoreBundle:Country',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c');
//                        ->where('c.parentCategory IS NOT NULL');
                },
                'required' => false
            ));
        $builder->add(
            'newsletter',
            'checkbox',
            array('required' => false)
                );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\BaseActor'
        ));
    }

}
