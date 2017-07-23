<?php
namespace CoreBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use CoreBundle\Entity\NewsletterShipping;

/**
 * @ORM\Table(name="baseactor");
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\BaseActorRepository")
 * @UniqueEntity(fields="username", message="Username already taken")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"baseactor" = "BaseActor"})
 *
 */

class BaseActor implements UserInterface, EquatableInterface , \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     * @Assert\NotBlank(message="Please enter your email")
     */
    protected $email;

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
     * @ORM\Column(name="lastname", type="string", length=100, nullable=true)
     */
    private $lastname;
    
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
     * @ORM\Column(name="active", type="boolean")
     */
    protected $active;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deleted;
    
    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     * @ORM\JoinTable(name="role_actorrole")
     */
    public $roles;
    

    /** @ORM\Column(name="facebook_id", type="string", length=255, nullable=true) */
    protected $facebook_id;

    /** @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true) */
    protected $facebook_access_token;

    /** @ORM\Column(name="google_id", type="string", length=255, nullable=true) */
    protected $google_id;

    /** @ORM\Column(name="google_access_token", type="string", length=255, nullable=true) */
    protected $google_access_token;

    /** @ORM\Column(name="twitter_id", type="string", length=255, nullable=true) */
    protected $twitter_id;

    /** @ORM\Column(name="twitter_access_token", type="string", length=255, nullable=true) */
    protected $twitter_access_token;
    
    /** @ORM\Column(name="instagram_id", type="string", length=255, nullable=true) */
    protected $instagram_id;

    /** @ORM\Column(name="instagram_access_token", type="string", length=255, nullable=true) */
    protected $instragram_access_token;
    
    /** @ORM\Column(name="test_connect_id", type="string", length=255, nullable=true) */
    protected $test_connect_id;

    /** @ORM\Column(name="test_connect_access_token", type="string", length=255, nullable=true) */
    protected $test_connect_access_token;
    
    /**
     * @var Dinamyc
     */
    protected $posts;
    
    /**
     * @var Dinamyc
     */
    protected $transactions;
    
    /**
     * @var Dinamyc
     */
    protected $addresses;
    
    public function __construct()
    {
        $this->active = false;
        $this->salt = md5(uniqid(null, true));
        $this->setCreated(new \DateTime());
        $this->roles = new ArrayCollection();
        $this->newsletter = false;
        $this->transactions = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedValue()
    {
        $this->setCreated(new \DateTime());
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function setEmail($email)
    {
        if($this->username=='')$this->username=$email;
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
     * Set lastname
     *
     * @param string $lastname
     *
     * @return BaseActor
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Get full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->name . ' ' . $this->lastname;
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
     * @inheritDoc
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @inheritDoc
     */
    public function isActive()
    {
        return $this->active;
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

    /**
     * Set deleted
     *
     * @param datetime $deleted
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Get deleted
     *
     * @return datetime
     */
    public function getDeleted()
    {
        return $this->deleted;
    }
    
    /**
     * Add role
     *
     * @param CoreBundle\Entity\Role $roles
     */
    public function addRole(\CoreBundle\Entity\Role $role)
    {
        $this->roles[] = $role;
    }

    /**
     * Remove role
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }
    
    /**
     * Get roles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }
    
    /**
     * Get roles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Set twitter_id
     *
     * @param integer $twitter_id
     */
    public function setTwitterId($twitter_id)
    {
        $this->twitter_id = $twitter_id;

        return $this;
    }

    /**
     * Get twitter_id
     *
     * @return integer
     */
    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    /**
     * Set twitter_access_token
     *
     * @param integer $twitter_access_token
     */
    public function setTwitterAccessToken($twitter_access_token)
    {
        $this->twitter_access_token = $twitter_access_token;

        return $this;
    }

    /**
     * Get twitter_access_token
     *
     * @return integer
     */
    public function getTwitterAccessToken()
    {
        return $this->twitter_access_token;
    }

    /**
     * Set facebook_id
     *
     * @param integer $facebook_id
     */
    public function setFacebookId($facebook_id)
    {
        $this->facebook_id = $facebook_id;

        return $this;
    }

    /**
     * Get facebook_id
     *
     * @return integer
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * Set facebook_access_token
     *
     * @param integer $facebook_access_token
     */
    public function setFacebookAccessToken($facebook_access_token)
    {
        $this->facebook_access_token = $facebook_access_token;

        return $this;
    }

    /**
     * Get facebook_access_token
     *
     * @return facebook_access_token
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * Set google_id
     *
     * @param integer $google_id
     */
    public function setGoogleId($google_id)
    {
        $this->google_id = $google_id;

        return $this;
    }

    /**
     * Get google_id
     *
     * @return integer
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * Set google_access_token
     *
     * @param integer $google_access_token
     */
    public function setGoogleAccessToken($google_access_token)
    {
        $this->google_access_token = $google_access_token;

        return $this;
    }

    /**
     * Get google_access_token
     *
     * @return integer
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }
    
    /**
     * Set instagram_id
     *
     * @param integer $instagram_id
     */
    public function setInstagramId($instagram_id)
    {
        $this->instagram_id = $instagram_id;

        return $this;
    }

    /**
     * Get instagram_id
     *
     * @return integer
     */
    public function getInstagramId()
    {
        return $this->instagram_id;
    }

    /**
     * Set instagram_access_token
     *
     * @param integer $instagram_access_token
     */
    public function setInstagramAccessToken($instagram_access_token)
    {
        $this->instagram_access_token = $instagram_access_token;

        return $this;
    }

    /**
     * Get instagram_access_token
     *
     * @return integer
     */
    public function getInstagramAccessToken()
    {
        return $this->instagram_access_token;
    }
    
    /**
     * Set test_connect_id
     *
     * @param integer $test_connect_id
     */
    public function setTestConnectId($test_connect_id)
    {
        $this->test_connect_id = $test_connect_id;

        return $this;
    }

    /**
     * Get test_connect_id
     *
     * @return integer
     */
    public function getTestConnectId()
    {
        return $this->test_connect_id;
    }
    
    /**
     * Set test_connect_access_token
     *
     * @param integer $test_connect_access_token
     */
    public function setTestConnectAccessToken($test_connect_access_token)
    {
        $this->test_connect_access_token = $test_connect_access_token;

        return $this;
    }

    /**
     * Get test_connect_access_token
     *
     * @return integer
     */
    public function getTestConnectAccessToken()
    {
        return $this->test_connect_access_token;
    }
    
    /**
     * Get roles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getRolesEntities()
    {
        return $this->roles;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->salt,
            $this->password,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->salt,
            $this->password,
        ) = unserialize($serialized);
    }

    public function isGranted($role)
    {
        return in_array($role, $this->getRoles());
    }
    
    /**
     * Add post
     *
     * @param Post $post
     *
     * @return Category
     */
    public function addPost($post)
    {
        $this->posts->add($post);

        return $this;
    }

    /**
     * Remove post
     *
     * @param Post $post
     */
    public function removePost($post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts
     *
     * @return ArrayCollection
     */
    public function getPosts()
    {
        return $this->posts;
    }
    
    /**
     * Add transaction
     *
     * @param Transaction $transaction
     *
     * @return BaseActor
     */
    public function addTransaction($transaction)
    {
        $this->transactions->add($transaction);

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param Transaction $transaction
     */
    public function removeTransaction($transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * Get transactions
     *
     * @return ArrayCollection
     */
    public function getTransactions()
    {
        return $this->transactions;
    }
    /**
     * Add address
     *
     * @param Address $address
     *
     * @return BaseActor
     */
    public function addAddress($address)
    {
        $this->addresses->add($address);

        return $this;
    }

    /**
     * Remove transaction
     *
     * @param Address $address
     */
    public function removeAddress($address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return ArrayCollection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }
    
}
