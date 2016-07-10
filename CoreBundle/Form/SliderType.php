<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SliderType
 */
class SliderType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('caption', null, array(
                'required' => false
            ))
            ->add('openInNewWindow', null, array(
                'required' => false
            ))
            ->add('url', 'url')
            ->add('active', null, array(
                'required' => false
            ))
            ->add('order')
            ->add('image', new ImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ))
//            ->add('price', 'text', array(
//                'required' => false
//            ))
                ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' =>  'CoreBundle\Entity\Slider',
            'cascade_validation' => true,
        ));
    }
}
