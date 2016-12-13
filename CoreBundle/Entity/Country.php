<?php
namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="country")
 */
class Country
{

    /**
    * @ORM\Id
    * @ORM\Column(type="string", length=2)
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=48, unique=true)
     */
    private $name;

    /**
    * @ORM\Column(type="string", length=3, unique=true)
    */
    protected $iso3;

   /**
    * @ORM\Column(type="string", length=3)
    */
    protected $currency;

   /**
     * @ORM\Column(name="is_active", type="boolean", options={"default" = false})
     */
    protected $isActive;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="State", mappedBy="country", cascade={"remove"})
     */
    private $states;
    
    
    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set iso3
     *
     * @param string $iso3
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    }

    /**
     * Get iso3
     *
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * Set currency
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function __toString()
    {
          return $this->getName();
    }

         /**
     * Set isActive
     *
     * @return RelationStore
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set state
     *
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }
    
    /*
     * INTERFACE METHODS
     */

    public function getKeyAttribute()
    {
        return "id";
    }

    public function getValueAttribute()
    {
        return "name";
    }

}
