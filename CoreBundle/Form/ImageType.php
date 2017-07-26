<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class ImageType
 */
class ImageType extends AbstractType
{
 
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $conf = array();
        if(isset($options['uploadDir'])){
            $conf['data'] = $options['uploadDir'];
        }
        $builder
            ->add('file', null, array(
                'label' => 'image.singular',
            ))
            ->add('uploadDir', HiddenType::class, $conf)
            ;
        
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Image',
            'uploadDir' => null
        ));
    }

}
