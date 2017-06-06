<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use CoreBundle\Entity\Image;


/**
 * Page Entity class
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
     * @ORM\Column(name="legal", type="boolean")
     */
    private $legal;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cookie", type="boolean")
     */
    private $cookie;
    
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
     * Is legal?
     *
     * @return boolean
     */
    public function isLegal()
    {
        return $this->legal;
    }

    /**
     * Set legal
     *
     * @param boolean $legal
     *
     * @return Page
     */
    public function setLegal($legal)
    {
        $this->legal = $legal;

        return $this;
    }
       
    /**
     * Is cookie?
     *
     * @return boolean
     */
    public function isCookie()
    {
        return $this->cookie;
    }

    /**
     * Set cookie
     *
     * @param boolean $cookie
     *
     * @return Page
     */
    public function setCookie($cookie)
    {
        $this->cookie = $cookie;

        return $this;
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
     * @return Page
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