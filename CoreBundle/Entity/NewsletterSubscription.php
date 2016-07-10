<?php
namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use EcommerceBundle\Entity\Address;
use EcommerceBundle\Entity\Transaction;
use Doctrine\Common\Collections\ArrayCollection;


//CREATE ALGORITHM = UNDEFINED VIEW `newsletter_subscription` (
//id,
//name,
//email,
//role
//) AS SELECT a.id, a.name, ba.email, r.name
//FROM actor AS a
//JOIN baseactor AS ba ON ba.id = a.id
//JOIN role_actorrole AS ar ON ba.id = ar.base_actor_id
//JOIN role AS r ON ar.role_id = r.id
//WHERE newsletter =1
//UNION 
//SELECT o.id, o.name, ba.email, r.name
//FROM optic AS o
//JOIN baseactor AS ba ON ba.id = o.id
//JOIN role_actorrole AS ar ON ba.id = ar.base_actor_id
//JOIN role AS r ON ar.role_id = r.id
//WHERE newsletter =1

        
/**
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\NewsletterSubscriptionRepository")
 * @ORM\Table(name="newsletter_subscription")
 */
class NewsletterSubscription 
{


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $email;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $role;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
    
}
