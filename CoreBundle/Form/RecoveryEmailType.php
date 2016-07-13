<?php
namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType as EType;

/**
 * Class RecoveryEmailType
 */
class RecoveryEmailType extends AbstractType
{
  
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EType::class, array('required' => true))
            ;
    }

}
