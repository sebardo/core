<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use CoreBundle\Entity\Image;


/**
 * MenuItem Entity class
 *
 * @ORM\Table(name="page")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\PageRepository")
 * 
 */
class Page
{
    use \A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;


    /**
     * @Assert\Valid
     */
    protected $translations;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->active = false;
        $this->translations = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if(is_object($this->translations->first())){
            return $this->translations->first()->getName();
        }else{
            return '';
        }
    }
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    
    /**
     * Is active?
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return MenuItem
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
        
    public function getTranslations()
    {
        return $this->translations;
    }

    public function setTranslations($translations)
    {
        $this->translations = $translations;
        return $this;
    }
    
}