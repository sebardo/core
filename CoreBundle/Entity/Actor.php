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
    
//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="\EcommerceBundle\Entity\Address", mappedBy="actor", cascade={"persist", "remove"})
//     */
//    private $addresses;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;
    
//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="\EcommerceBundle\Entity\Transaction", mappedBy="actor", cascade={"remove"})
//     */
//    private $transactions;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\BlogBundle\Entity\Post", mappedBy="actor", cascade={"remove"})
     */
    private $posts;
    
//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="\EcommerceBundle\Entity\Advert", mappedBy="actor", cascade={"remove"})
//     */
//    private $adverts;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\CoreBundle\Entity\NewsletterShipping", mappedBy="actor", cascade={"remove"})
     */
    private $shippings;
    
//    /**
//     * @var ArrayCollection
//     *
//     * @ORM\OneToMany(targetEntity="\BlogBundle\Entity\Comment", mappedBy="actor", cascade={"remove"})
//     */
//    private $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->newsletter = false;
//        $this->addresses = new ArrayCollection();
//        $this->transactions = new ArrayCollection();
        $this->posts = new ArrayCollection();
//        $this->adverts = new ArrayCollection();
        $this->shippings = new ArrayCollection();
//        $this->comments = new ArrayCollection();
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
//
//    /**
//     * Add address
//     *
//     * @param Address $address
//     *
//     * @return Actor
//     */
//    public function addAddress(Address $address)
//    {
//        $address->setActor($this);
//        $this->addresses->add($address);
//
//        return $this;
//    }
//
//    /**
//     * Remove address
//     *
//     * @param Address $address
//     */
//    public function removeAddress(Address $address)
//    {
//        $this->addresses->removeElement($address);
//    }
//
//    /**
//     * Get addresses
//     *
//     * @return ArrayCollection
//     */
//    public function getAddresses()
//    {
//        return $this->addresses;
//    }
//
//    /**
//     * Add transaction
//     *
//     * @param Transaction $transaction
//     *
//     * @return Actor
//     */
//    public function addTransaction(Transaction $transaction)
//    {
//        $transaction->setActor($this);
//        $this->transactions->add($transaction);
//
//        return $this;
//    }
//
//    /**
//     * Remove transaction
//     *
//     * @param Transaction $transaction
//     */
//    public function removeTransaction(Transaction $transaction)
//    {
//        $this->transactions->removeElement($transaction);
//    }
//
//    /**
//     * Get transaction
//     *
//     * @return ArrayCollection
//     */
//    public function getTransactions()
//    {
//        return $this->transactions;
//    }

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
     * Add post
     *
     * @param Post $post
     *
     * @return Actor
     */
    public function addPost(Post $post)
    {
        $post->setActor($this);
        $this->posts->add($post);

        return $this;
    }

    /**
     * Remove post
     *
     * @param Post $post
     */
    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get post
     *
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }
//    
//    /**
//     * Add advert
//     *
//     * @param Advert $advert
//     *
//     * @return Actor
//     */
//    public function addAdvert(Advert $advert)
//    {
//        $advert->setActor($this);
//        $this->adverts->add($advert);
//
//        return $this;
//    }
//
//    /**
//     * Remove advert
//     *
//     * @param Advert $advert
//     */
//    public function removeAdvert(Advert $advert)
//    {
//        $this->adverts->removeElement($advert);
//    }
//
//    /**
//     * Get advert
//     *
//     * @return ArrayCollection
//     */
//    public function getAdverts()
//    {
//        return $this->adverts;
//    }
//    
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
    
    /**
     * Add comment
     *
     * @param Comment $comment
     *
     * @return Actor
     */
    public function addComment(Comment $comment)
    {
        $comment->setActor($this);
        $this->comments->add($comment);

        return $this;
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comment
     *
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
