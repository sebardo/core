<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailType
 */
class EmailType extends AbstractType
{
    
    protected $email;
    
    public function __construct($config) {
        if(isset($config['email']))
        $this->email = $config['email'];
    }
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = array();
        if($this->email!='') {
            $data['data'] = $this->email;
        }
        $builder
            ->add('to', null, $data)
            ->add('subject')
            ->add('body', 'textarea')
            ->add('email', 'hidden', $data)
            ;
    }

}
