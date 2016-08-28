<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Class BootstrapVariablesType
 */
class BootstrapVariablesType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $lessContent = file_get_contents(__DIR__.'/../Resources/public/less/custom-fonts.less');
        $builder
            ->add('vars', TextareaType::class, array(
                'data' => $lessContent
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
//            'data_class' =>  'CoreBundle\Entity\MenuItem',
        ));
    }

}
