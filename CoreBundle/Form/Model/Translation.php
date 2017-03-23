<?php

namespace CoreBundle\Form\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * TranslationLabel Model class
 *
 */
class Translation
{
    use \A2lix\I18nDoctrineBundle\Doctrine\ORM\Util\Translatable;
    
    private $key;
        
    private $domain;

    protected $translations;

    
    /**
     * Constructor
     */
    public function __construct()
    {
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
     * Get key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set key
     *
     * @param string $key
     *
     * @return TranslationLabel
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }
    
    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set domain
     *
     * @param boolean $domain
     *
     * @return TranslationLabel
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

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