<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use CoreBundle\Form\ImageType;
use Symfony\Component\Yaml\Yaml;

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
                $icons['fa-'.$value['id']] = $value['name'];
        }
        $builder
            ->add('name')
            ->add('shortDescription')
            ->add('description')
            ->add('visible', null, array('required' => false))
            ->add('active', null, array('required' => false))
            ->add('icon', 'choice', array(
                    'choices' => $icons
                ))
            ->add('metaTitle')
            ->add('metaDescription')
            ->add('metaTags')
            ->add('parentMenuItem', 'entity', array(
                'class' => 'CoreBundle:MenuItem',
                'required' => false
            ))
            ->add('image', new ImageType(), array(
                'error_bubbling' => false,
                'required' => false
            ))
            ->add('removeImage', 'hidden', array( 'attr' => array(
                'class' => 'remove-image'
                )))
            ->add('url')
            ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\MenuItem',
            //'cascade_validation' => true,
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'core_menuitemtype';
    }
}
