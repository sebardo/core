<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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
            ->add('lastname')
            ->add('username')
            ->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('email')
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ->add('removeImage', HiddenType::class, array( 'attr' => array(
                'class' => 'remove-image'
                )))
            ->add('active', null, array('required' => false))
            ->add('newsletter', null, array('required' => false))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\BaseActor',
            'use_ecommerce' => null,
            'token_storage' => null
        ));
    }
}
