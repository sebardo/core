<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EmailType
 */
class EmailType extends AbstractType
{
  
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = array();
        if($options['email'] != '' ) {
            $data['data'] = $options['email'];
        }
        $builder
            ->add('to', null, $data)
            ->add('subject')
            ->add('body', TextareaType::class)
            ->add('email', HiddenType::class, $data)
            ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
            $resolver->setDefaults(
                array(
                    'email' => null,
                )
            );
        
    }

}
