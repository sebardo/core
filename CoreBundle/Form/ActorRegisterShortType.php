<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use CoreBundle\Form\ImageType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType as SymfonyPassworType;

class ActorRegisterShortType extends AbstractType
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
            ->add('password', SymfonyPassworType::class, array(
                'invalid_message' => 'The password fields must match.',
                'required' => true,
                'label' => 'Password',
            ))
            ->add('email')
            ->add('newsletter', null, array('required' => false))    
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
