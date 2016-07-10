<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use CoreBundle\Form\ImageType;

class ActorType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('surnames')
            ->add('username')
            ->add('password', 'password', array('required' => false))
            ->add('email')
            ->add('image', new ImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ))
            ->add('isActive', 'checkbox', array('required' => false))
            ->add('newsletter', 'checkbox', array('required' => false))    
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Actor'
        ));
    }

}
