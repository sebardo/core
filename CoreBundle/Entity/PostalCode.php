<?php
namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
//use EcommerceBundle\Entity\Advert;

/**
 * @ORM\Entity
 * @ORM\Table(name="postal_code")
 */
class PostalCode
{

    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;

    /**
     * @ORM\Column(type="string", length=48, unique=false)
     */
    private $postalCode;
    
    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $country;
    
    /**
     * @ORM\Column(type="string", length=48)
     */
    private $city;
    
    /**
     * @ORM\Column(type="string", length=48)
     */
    private $region;
    
    /**
     * @var State
     *
     * @ORM\ManyToOne(targetEntity="State")
     * @ORM\JoinColumn(name="state_id", referencedColumnName="id", nullable=true, onDelete="set null")
     */
    private $state;
    
    /**
    * @ORM\Column(type="string", length=2)
    */
    protected $stateIso;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="string", length=100, nullable=true)
     */
    private $latitude;

     /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="string", length=100, nullable=true)
     */
    private $longitude;

   /**
     * @ORM\Column(type="boolean", options={"default" = true})
     */
    protected $active;
    
//    /**
//    * @ORM\ManyToMany(targetEntity="\EcommerceBundle\Entity\Advert", mappedBy="postalCodes")
//    */
//    protected $adverts;

    /**
     * Constructor
     */
    public function __construct()
    {
//        $this->adverts = new ArrayCollection();
    }
    
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
     * Set postalCode
     *
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
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
    
    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
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
     * Set region
     *
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
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

    /**
     * Set stateIso
     *
     * @param string $stateIso
     */
    public function setStateIso($stateIso)
    {
        $this->iso3 = $stateIso;
    }

    /**
     * Get stateIso
     *
     * @return string
     */
    public function getStateIso()
    {
        return $this->stateIso;
    }

    /**
     * Set latitude
     *
     * @param string $latitude
     *
     * @return Optic
     */
    public function setLatitude($latitude)
    {
        $this->latitude= $latitude;

        return $this;
    }
    
    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    /**
     * Set longitude
     *
     * @param string $longitude
     *
     * @return Optic
     */
    public function setLongitude($longitude)
    {
        $this->longitude= $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    public function __toString()
    {
          return $this->getPostalCode();
    }

         /**
     * Set isActive
     *
     * @return RelationStore
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }
//    
//    /**
//     * Add advert
//     *
//     * @param Adverttising $advert
//     *
//     * @return Located
//     */
//    public function addAdvert(Advert $advert)
//    {
//        $this->adverts[] = $advert;
//
//        return $this;
//    }
//
//    /**
//     * Remove $advert
//     *
//     * @param Advert $advert
//     */
//    public function removeAdvert(Advert $advert)
//    {
//        $this->adverts->removeElement($advert);
//    }
//
//    /**
//     * Get adverts
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getAdverts()
//    {
//        return $this->adverts;
//    } 

}
