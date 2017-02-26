<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationLabelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('key')->add('visible')->add('active')
                ->add('translations', 'A2lix\TranslationFormBundle\Form\Type\TranslationsType', array(
                    'fields' => array(                               
                        'key' => array(                       
                            'required' => true
                        ),
                    ),
                ))
                ->add('visible', null, array('required' => false))
                ->add('active', null, array('required' => false))
                ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\TranslationLabel'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_translationlabel';
    }


}
