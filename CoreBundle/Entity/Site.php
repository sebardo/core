<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Entity\Image;

/**
 * Site Entity class
 *
 * @ORM\Table(name="site")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\SiteRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Site  
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=10000, nullable=true)
     */
    private $description;
    
    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $image;

    public $removeImage;
    
    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $logo;
    
    /**
     * @var Image
     *
     * @ORM\OneToOne(targetEntity="CoreBundle\Entity\Image", cascade={"persist", "remove"})
     * @ORM\JoinColumn(onDelete="set null")
     */
    private $logoFooter;
    
    public $removeLogoFooter;
    
    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, unique=false)
     * @ORM\Column(length=255, unique=false)
     */
    private $slug;
    
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
     * @return Site
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
     * Set title
     *
     * @param string $title
     *
     * @return Site
     */
    function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Get title
     *
     * @return string
     */
    function getTitle() {
        return $this->title;
    }
    
    /**
     * Set Description
     *
     * @param string $description
     *
     * @return Site
     */
    function setDescription($description) {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Get Description
     *
     * @return string
     */
    function getDescription() {
        return $this->description;
    }

    /**
     * Set Image
     *
     * @param Image $image
     *
     * @return Site
     */
    function setImage($image) {
        $this->image = $image;
        return $this;
    }
    
    /**
     * Get Image
     *
     * @return string
     */
    
    function getImage() {
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
     * Set logo
     *
     * @return string
     */
    function setLogo($logo) {
        $this->logo = $logo;
        return $this;
    }
    
    /**
     * Get logo
     *
     * @return string
     */
    function getLogo() {
        return $this->logo;
    }
    
    /**
     * Set logoFooter
     *
     * @return string
     */
    function setLogoFooter($logoFooter) {
        $this->logoFooter = $logoFooter;
        return $this;
    }
    
    /**
     * Get logoFooter
     *
     * @return string
     */
    function getLogoFooter() {
        return $this->logoFooter;
    }
    
    public function setRemoveLogoFooter($removeLogoFooter)
    {
        $this->removeLogoFooter = $removeLogoFooter;

        return $this->removeLogoFooter;
    }

    public function getRemoveLogoFooter()
    {
        return $this->removeLogoFooter;
    }
    
    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Site
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
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    } 
    
}
