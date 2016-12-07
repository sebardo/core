<?php

namespace CoreBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationShort
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

}