<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreBundle\Form\ImageType;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class MenuItemType
 */
class MenuItemType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        
        
        $value = Yaml::parse(file_get_contents(__DIR__.'/../../../../../web/bundles/admin/plugins/font-awesome-4.6.3/src/icons.yml'));

        $icons = array();
        foreach ($value['icons'] as $value) {
                $icons[$value['id']] = $value['name'];
        }
        $builder
            ->add('name')
            ->add('shortDescription')
            ->add('description')
            ->add('visible', null, array('required' => false))
            ->add('active', null, array('required' => false))
            ->add('icon', ChoiceType::class, array(
                    'choices' => $icons,
                    'choices_as_values' => false,
                ))
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('metaTags')
            ->add('parentMenuItem', EntityType::class, array(
                'class' => 'CoreBundle:MenuItem',
                'required' => false
            ))
            ->add('image', ImageType::class, array(
                'required' => false
            ))
            ->add('removeImage', HiddenType::class, array( 'attr' => array(
                'class' => 'remove-image'
                )))
            ->add('url', UrlType::class, array('required' => false))
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
