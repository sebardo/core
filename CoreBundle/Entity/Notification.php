<?php
namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Actor;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\NotificationRepository")
 * @ORM\Table(name="notification")
 */
class Notification
{
    
    const TYPE_NEW_OPTIC = 'new_optic';
    const TYPE_NEW_PRODUCT = 'new_product';
    const TYPE_ADD_WEB = 'add_web';
    const TYPE_ADD_PRODUCT = 'add_product';
    const TYPE_MOST_VISITED = 'most_visited';
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\BaseActor")
     * @ORM\JoinColumn(name="actor_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $actor;
    
    /**
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\BaseActor")
     * @ORM\JoinColumn(name="actordest_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $actorDest;
    
    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="text")
     */
    protected $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $detail;
    
    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;
    
    /**
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    public function __construct()
    {
        $this->isActive = true;
        $this->setCreated(new \DateTime());
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
     * Set actor
     *
     * @param entity $actor
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Get actor
     *
     * @return string
     */
    public function getActor()
    {
        return $this->actor;
    }

    
    /**
     * Set actorDest
     *
     * @param entity $actorDest
     */
    public function setActorDest(Actor $actorDest)
    {
        $this->actorDest = $actorDest;
    }

    /**
     * Get actorDest
     *
     * @return entity
     */
    public function getActorDest()
    {
        return $this->actorDest;
    }
    
    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type) 
    {
        $this->type = $type;
    }
    
    /**
     * Get type
     *
     * @return string
     */
    public function getType() 
    {
        return $this->type;
    }

    /**
     * Set detail
     *
     * @param string $detail
     */
    public function setDetail($detail) 
    {
        $this->detail = $detail;
    }
    
    /**
     * Get detail
     *
     * @return string
     */
    public function getDetail() 
    {
        return json_decode($this->detail);
    }
    
    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setActive($isActive) 
    {
        $this->isActive = $isActive;
    }
    
    /**
     * Get isActive
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }
    
    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

}
