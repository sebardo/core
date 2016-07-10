<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RecoveryPasswordType extends AbstractType
{
    public function __construct($options=null)
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('password', 'repeated', array(
           'first_name'  => 'password',
           'second_name' => 'confirm',
           'type'        => 'password',
            'attr' => array('class' => 'form-control')
        ));
        $builder->add('hash', 'hidden', array('data' => (isset($this->options['hash'])? $this->options['hash']: null)));
    }

}
