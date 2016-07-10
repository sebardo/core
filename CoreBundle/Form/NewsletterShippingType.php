<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use CoreBundle\Entity\NewsletterShipping;

/**
 * Class NewsletterShippingType
 */
class NewsletterShippingType extends AbstractType
{
    protected $config;
    
    public function __construct($params=array()) {
        $this->config = $params;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newsletter', 'entity', array(
                'class' => 'CoreBundle:Newsletter',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.visible = 1');
                },
                'required' => false
            ));
        if(isset($this->config['token'])){
            $builder->add('type', 'choice', array(
                    'label' => 'newsletter.shipping.type',
                    'choices' => array(
                         NewsletterShipping::TYPE_TOKEN => 'Enviar comunicado relanzamiento',
                    )
                )
            )
            ->add('inactive','checkbox', array('required' => false, 'data' => true ))
                
            ;
        } else{
            $builder->add('type', 'choice', array(
                    'label' => 'newsletter.shipping.type',
                    'choices' => array(
                         NewsletterShipping::TYPE_SUBSCRIPTS => 'Enviar a todos los suscriptores',
                         NewsletterShipping::TYPE_OPTICS => 'Enviar a las Ópticas',
                         NewsletterShipping::TYPE_USER => 'Enviar a los usuarios',
                         NewsletterShipping::TYPE_TOKEN => 'Enviar comunicado relanzamiento',
                    )
                )
            )
            ;
        }
        
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\NewsletterShipping',
            'cascade_validation' => true,
        ));
    }
}
