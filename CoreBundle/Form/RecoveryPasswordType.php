<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType as PType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RecoveryPasswordType extends AbstractType
{
    public function __construct($options=null)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        $builder->add('password', RepeatedType::class, array(
                'type' => PType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ));
        $builder->add('hash', HiddenType::class, array('data' => (isset($options['hash'])? $options['hash']: null)));
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'hash' => null,
            )
        );
    }

}
