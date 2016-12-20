<?php

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use CoreBundle\Entity\Actor;

/**
 * Visit
 *
 * @ORM\Table(name="visit")
 * @ORM\Entity(repositoryClass="CoreBundle\Entity\Repository\VisitRepository")
 */
class Visit
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_from", type="datetime")
     */
    private $dateFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_to", type="datetime")
     */
    private $dateTo;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;
    
    /**
     * @var boolean
     *
     * @ORM\Column(name="sent", type="boolean")
     */
    private $sent;

    /**
     * @var boolean
     *
     * @ORM\Column(name="feedback", type="boolean")
     */
    private $feedback;
    
    /**
     * @var Actor
     *
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\EmailToken")
     * @ORM\JoinColumn(nullable=true)
     */
    private $emailToken;
    
     /**
     * @var Actor
     *
     * @ORM\ManyToOne(targetEntity="CoreBundle\Entity\Actor")
     * @ORM\JoinColumn(nullable=true)
     */
    private $actor;

    
    public function __toString() {
        return $this->actor->getFullName();
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
     * Set dateFrom
     *
     * @param \DateTime $dateFrom
     *
     * @return Visit
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * Get dateFrom
     *
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * Set dateTo
     *
     * @param \DateTime $dateTo
     *
     * @return Visit
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * Get dateTo
     *
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Visit
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
    
    /**
     * Set sent
     *
     * @param boolean $sent
     *
     * @return Visit
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return boolean
     */
    public function getSent()
    {
        return $this->sent;
    }
    
    /**
     * Set feedback
     *
     * @param boolean $feedback
     *
     * @return Visit
     */
    public function setFeedback($feedback)
    {
        $this->feedback = $feedback;

        return $this;
    }

    /**
     * Get feedback
     *
     * @return boolean
     */
    public function getFeedback()
    {
        return $this->feedback;
    }
    
    /**
     * Set actor
     *
     * @param Actor $actor
     *
     * @return Visit
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;

        return $this;
    }

    /**
     * Get actor
     *
     * @return Actor
     */
    public function getActor()
    {
        return $this->actor;
    }
    
    /**
     * Set emailToken
     *
     * @param EmailToken $emailToken
     *
     * @return Visit
     */
    public function setEmailToken($emailToken)
    {
        $this->emailToken = $emailToken;

        return $this;
    }

    /**
     * Get emailToken
     *
     * @return EmailToken
     */
    public function getEmailToken()
    {
        return $this->emailToken;
    }
}

