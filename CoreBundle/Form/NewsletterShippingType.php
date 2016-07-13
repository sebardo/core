<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use CoreBundle\Entity\NewsletterShipping;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class NewsletterShippingType
 */
class NewsletterShippingType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('newsletter', EntityType::class, array(
                'class' => 'CoreBundle:Newsletter',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.visible = 1');
                },
                'required' => false
            ));
        if(isset($options['token'])){
            $builder->add('type', ChoiceType::class, array(
                    'label' => 'newsletter.shipping.type',
                    'choices' => array(
                        'Enviar comunicado relanzamiento' => NewsletterShipping::TYPE_TOKEN,
                    ),
                    'choices_as_values' => true
                )
            )
            ->add('inactive', CheckboxType::class, array('required' => false, 'data' => true ))
                
            ;
        } else{
            $builder->add('type', ChoiceType::class, array(
                    'label' => 'newsletter.shipping.type',
                    'choices' => array(
                         'Enviar a todos los suscriptores' => NewsletterShipping::TYPE_SUBSCRIPTS,
                         'Enviar a los usuarios' => NewsletterShipping::TYPE_USER ,
                         'Enviar comunicado relanzamiento' => NewsletterShipping::TYPE_TOKEN,
                    ),
                    'choices_as_values' => true
                )
            );
        }
        
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\NewsletterShipping',
        ));
    }
}
