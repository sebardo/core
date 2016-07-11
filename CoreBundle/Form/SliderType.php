<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

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
            ->add('url', UrlType::class, array(
                'required' => false
            ))
            ->add('active', null, array(
                'required' => false
            ))
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' =>  'CoreBundle\Entity\Slider',
        ));
    }
}
