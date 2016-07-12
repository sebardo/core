<?php

namespace CoreBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\State;
use CoreBundle\Entity\Country;

class Registration
{
    /**
     * @Assert\Type(type="\CoreBundle\Entity\Actor")
     * @Assert\Valid()
     */
    protected $actor;

    /**
     * @Assert\NotBlank()
     * @Assert\IsTrue()
     */
    protected $termsAccepted;
   
    /**
     *  @Assert\NotBlank()
     */
    protected $city;

    /**
     *  @Assert\NotBlank()
     */
    private $state;

    /**
     *  @Assert\NotBlank()
     */
    private $country; 
    
    public function setActor(\CoreBundle\Entity\Actor $actor)
    {
        $this->actor = $actor;
    }

    public function getActor()
    {
        return $this->actor;
    }

    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted($termsAccepted)
    {
        $this->termsAccepted = (Boolean) $termsAccepted;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return Actor
     */
    public function setState(State $state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set country
     *
     * @param integer $country
     *
     * @return Actor
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return integer
     */
    public function getCountry()
    {
        return $this->country;
    }
    
}
