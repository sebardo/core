<?php
namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Entity\NewsletterShipping;
use BlogBundle\Entity\Post;
use BlogBundle\Entity\Comment;
//use EcommerceBundle\Entity\Address;
//use EcommerceBundle\Entity\Transaction;
//use EcommerceBundle\Entity\Advert;


/**
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\ActorRepository")
 * @ORM\Table(name="actor")
 */
class Actor extends BaseActor
{


    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surnames", type="string", length=100, nullable=true)
     */
    private $surnames;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="newsletter", type="boolean")
     */
    private $newsletter;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;

    public $removeImage;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\CoreBundle\Entity\NewsletterShipping", mappedBy="actor", cascade={"remove"})
     */
    private $shippings;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->newsletter = false;
        $this->shippings = new ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Actor
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * Set surnames
     *
     * @param string $surnames
     *
     * @return Actor
     */
    public function setSurnames($surnames)
    {
        $this->surnames = $surnames;

        return $this;
    }

    /**
     * Get surnames
     *
     * @return string
     */
    public function getSurnames()
    {
        return $this->surnames;
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->name . ' ' . $this->surnames;
    }

     /**
     * Set image
     *
     * @param Image $image
     *
     * @return Category
     */
    public function setImage(Image $image = null)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }
     
    
    public function setRemoveImage($removeImage)
    {
        $this->removeImage = $removeImage;

        return $this->removeImage;
    }

    public function getRemoveImage()
    {
        return $this->removeImage;
    }
    
    /**
     * Set newsletter
     *
     * @param boolean $newsletter
     *
     * @return User
     */
    public function setNewsletter($newsletter)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Is subscribed to newsletter?
     *
     * @return boolean
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
    
    /**
     * Add shipping
     *
     * @param Shipping $shipping
     *
     * @return Actor
     */
    public function addShipping(NewsletterShipping $shipping)
    {
        $shipping->setActor($this);
        $this->shippings->add($shipping);

        return $this;
    }

    /**
     * Remove shipping
     *
     * @param Shipping $shipping
     */
    public function removeShipping(NewsletterShipping $shipping)
    {
        $this->shippings->removeElement($shipping);
    }

    /**
     * Get shipping
     *
     * @return ArrayCollection
     */
    public function getShippings()
    {
        return $this->shippings;
    }

}
