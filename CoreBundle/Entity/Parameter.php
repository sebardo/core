<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Parameter Entity class
 *
 * 
 * @ORM\Table(name="parameter")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\ParameterRepository")
 */
class Parameter 
{
  
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Parameter key
     *
     * @var string
     * 
     * @ORM\Column(name="parameter", type="string")
     */
    protected $parameter;
    
    
   /**
     * Parameter value.
     *
     * @var string
     * 
     * @ORM\Column(name="value", type="text")
     */
    protected $value;

    public function __toString() {
        return $this->value ;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter()
    {
        return $this->parameter;
    }


    /**
     * {@inheritdoc}
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;

        return $this;
    }

     /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
    
}
