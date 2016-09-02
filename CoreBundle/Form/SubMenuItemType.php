<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;

/**
 * Class SubMenuItemType
 */
class SubMenuItemType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', 'A2lix\TranslationFormBundle\Form\Type\TranslationsType', array(
                'fields' => array(                               
                    'name' => array(                       
                        'required' => true
                    ),
                    'slug' => array(                         
                        'required' => false
                    ),
                    'shortDescription' => array(                         
                        'required' => true
                    ),
                    'description' => array(                         
                        'required' => true
                    ),
                    'metaTitle' => array(                         
                        'required' => true
                    ),
                    'metaDescription' => array(                         
                        'required' => true
                    ),
                ),
            ))
            ->add('metaTags')
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ->add('visible', null, array('required' => false))
            ->add('active', null, array('required' => false))
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' =>  'CoreBundle\Entity\MenuItem',
        ));
    }
}
