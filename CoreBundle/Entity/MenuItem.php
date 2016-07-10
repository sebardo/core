<?php

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;
use CoreBundle\Entity\Image;

/**
 * MenuItem Entity class
 *
 * @ORM\Table(name="menuitem")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\MenuItemRepository")
 * @Assert\Callback(methods={"validateParentMenuItem"})
 */
class MenuItem 
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255)
     */
    private $icon;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="text")
     * @Assert\NotBlank
     */
    private $shortDescription;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255)
     * @Assert\NotBlank
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text")
     * @Assert\NotBlank
     */
    private $metaDescription;
    

    /**
     * @var string
     *
     * @ORM\Column(name="meta_tags", type="string", length=255, nullable=true)
     */
    private $metaTags;

    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;

    public $removeImage;
    
    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;
    
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parentMenuItem", cascade={"remove"})
     */
    private $subitems;

    /**
     * @var MenuItem
     *
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="subitems")
     * @ORM\JoinColumn(name="parent_menuitem_id", referencedColumnName="id", nullable=true , onDelete="cascade")
     */
    private $parentMenuItem;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible;
    
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="_order", type="integer", nullable=true)
     */
    private $order;

    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->visible = false;
        $this->active = false;
        $this->subitems = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     *
     * @return MenuItem
     */
    public function setName($name)
    {
        $this->name = $name;
        if($this->metaTitle == '') $this->metaTitle = $this->name;

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
     * Set icon
     *
     * @param string $icon
     *
     * @return MenuItem
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string 
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return MenuItem
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
    
    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set shortDescription
     *
     * @param string $shortDescription
     *
     * @return MenuItem
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        if($this->shortDescription == '') $this->shortDescription = strip_tags(substr ($this->shortDescription, 0, 200));

        return $this;
    }

    /**
     * Get shortDescription
     *
     * @return string 
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }
    
    /**
     * Set description
     *
     * @param string $description
     *
     * @return MenuItem
     */
    public function setDescription($description)
    {
        $this->description = $description;
        if($this->metaDescription == '') $this->metaDescription = strip_tags(substr ($this->description, 0, 200));

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set metaTitle
     *
     * @param string $metaTitle
     *
     * @return MenuItem
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * Get metaTitle
     *
     * @return string 
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Set metaDescription
     *
     * @param string $metaDescription
     *
     * @return MenuItem
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * Get metaDescription
     *
     * @return string 
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set metaTags
     *
     * @param string $metaTags
     *
     * @return MenuItem
     */
    public function setMetaTags($metaTags)
    {
        $this->metaTags = $metaTags;

        return $this;
    }

    /**
     * Get metaTags
     *
     * @return string 
     */
    public function getMetaTags()
    {
        return $this->metaTags;
    }

    /**
     * Set image
     *
     * @param Image $image
     *
     * @return MenuItem
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
     * Set url
     *
     * @param string $url
     *
     * @return Slider
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Add menuItem
     *
     * @param MenuItem $menuItem
     *
     * @return MenuItem
     */
    public function addMeniItem(MenuItem $menuItem)
    {
        $this->subitems->add($menuItem);

        return $this;
    }

    /**
     * Remove menuItem
     *
     * @param MenuItem $menuItem
     */
    public function removeMenuItem(MenuItem $menuItem)
    {
        $this->subitems->removeElement($menuItem);
    }

    /**
     * Get subitems
     *
     * @return ArrayCollection
     */
    public function getSubitems()
    {
        return $this->subitems;
    }

    /**
     * Set parent MenuItem
     *
     * @param MenuItem $parentMenuItem
     *
     * @return MenuItem
     */
    public function setParentMenuItem(MenuItem $parentMenuItem = null)
    {
        $this->parentMenuItem = $parentMenuItem;

        return $this;
    }

    /**
     * Get parent MenuItem
     *
     * @return MenuItem
     */
    public function getParentMenuItem()
    {
        return $this->parentMenuItem;
    }
    
    /**
     * Is visible?
     *
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Set visible
     *
     * @param boolean $visible
     *
     * @return MenuItem
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }
    
    /**
     * Is active?
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return MenuItem
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     *
     * @return Slider
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    
    /**
     * Custom validator to check parent menuitem exclusion
     *
     * @param ExecutionContextInterface $context
     */
    public function validateParentMenuItem(ExecutionContextInterface $context)
    {
//        if (!$this->parentMenuItem) {
//            $context->addViolationAt('parentMenuItem', 'menuitem.missing.parent');
//        } 
    }
    
}