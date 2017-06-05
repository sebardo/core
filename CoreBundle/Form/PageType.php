<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', 'A2lix\TranslationFormBundle\Form\Type\TranslationsType', array(
                'fields' => array(                               
                    'title' => array(                       
                        'required' => true
                    ),
                    'slug' => array(                         
                        'required' => false
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
                    'metaTags' => array(                         
                        'required' => false
                    ),
                ),
            ))
            ->add('active')        
            ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Page'
        ));
    }


}
