<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Timestampable;

/**
 * EmailToken Entity class
 *
 * @ORM\Table(name="email_token")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\EmailTokenRepository")
 */
class EmailToken extends Timestampable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="token", type="string")
     */
    private $token;
    
    
    /**
     * @ORM\Column(type="string", length=60)
     */
    protected $email;

    /**
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active;
    
    public function __construct() {
        $this->active = false;
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
     * @inheritDoc
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @inheritDoc
     */
    public function getEmail()
    {
        return $this->email;
    }
    
     /**
     * @inheritDoc
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function getActive()
    {
        return $this->active;
    }
    
}