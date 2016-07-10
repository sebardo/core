<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;

/**
 * Class ActorEditType
 */
class ActorEditType extends AbstractType
{
    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Actor',
        ));
    }
}
