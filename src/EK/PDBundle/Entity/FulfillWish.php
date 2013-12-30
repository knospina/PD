<?php

namespace EK\PDBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Wish
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EK\PDBundle\Entity\WishRepository")
 */
class FulfillWish {

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
     * @ORM\Column(name="price", type="decimal", scale=2)
     */
    private $price;
    
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="fulfilledwishes")
     * @ORM\JoinColumn(name="ownerId", referencedColumnName="id")
     */
    protected $ownerId;
    
    /**
     * @ORM\ManyToOne(targetEntity="Wish", inversedBy="fulfilledwishes")
     * @ORM\JoinColumn(name="wishId", referencedColumnName="id")
     */
    protected $wishId;


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
     * Set price
     *
     * @param float $price
     * @return FulfillWish
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set ownerId
     *
     * @param \EK\PDBundle\Entity\User $ownerId
     * @return FulfillWish
     */
    public function setOwnerId(\EK\PDBundle\Entity\User $ownerId = null)
    {
        $this->ownerId = $ownerId;
    
        return $this;
    }

    /**
     * Get ownerId
     *
     * @return \EK\PDBundle\Entity\User 
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * Set wishId
     *
     * @param \EK\PDBundle\Entity\Wish $wishId
     * @return FulfillWish
     */
    public function setWishId(\EK\PDBundle\Entity\Wish $wishId = null)
    {
        $this->wishId = $wishId;
    
        return $this;
    }

    /**
     * Get wishId
     *
     * @return \EK\PDBundle\Entity\Wish 
     */
    public function getWishId()
    {
        return $this->wishId;
    }
}