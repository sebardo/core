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
        if(isset($options['translator'])){
            $this->translator = $options['translator'];
        }
        
        $builder
            ->add('translations', 'A2lix\TranslationFormBundle\Form\Type\TranslationsType', array(
                'fields' => array(                               
                    'title' => array(                       
                        'label' => $this->translator->trans('title'),
                    ),
                    'slug' => array(                         
                        'required' => false
                    ),
                    'description' => array( 
                        'label' => $this->translator->trans('description'),
                        'locale_options' => array(
                            'ca' => array(
                                'required' => false,
                            ),
                            'en' => array(
                                'required' => false,
                            ),
                            'es' => array(
                                'required' => false,
                            ),
                        ),
                    ),
                    'metaTitle' => array(                         
                        'label' => $this->translator->trans('metaTitle'),
                        'locale_options' => array(
                            'ca' => array(
                                'required' => false,
                            ),
                            'en' => array(
                                'required' => false,
                            ),
                            'es' => array(
                                'required' => false,
                            ),
                        ),
                    ),
                    'metaDescription' => array(                         
                        'label' => $this->translator->trans('metaDescription'),
                        'locale_options' => array(
                            'ca' => array(
                                'required' => false,
                            ),
                            'en' => array(
                                'required' => false,
                            ),
                            'es' => array(
                                'required' => false,
                            ),
                        ),
                    ),
                    'metaTags' => array(                         
                        'label' => $this->translator->trans('metaTags'),
                        'locale_options' => array(
                            'ca' => array(
                                'required' => false,
                            ),
                            'en' => array(
                                'required' => false,
                            ),
                            'es' => array(
                                'required' => false,
                            ),
                        ),
                    ),
                ),
            ))
            ->add('cookie')
            ->add('legal')
            ->add('active')
            ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Page',
            'translator' => null
        ));
    }


}
