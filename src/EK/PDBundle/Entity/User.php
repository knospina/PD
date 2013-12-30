<?php

namespace EK\PDBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection; 
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EK\PDBundle\Entity\UserRepository")
 */
class User {

    /**
     * @ORM\OneToMany(targetEntity="Wish", mappedBy="ownerId")
     */
    protected $wishes;
    
    /**
     * @ORM\OneToMany(targetEntity="FulfillWish", mappedBy="ownerId")
     */
    protected $fulfilledwishes;

    public function __construct() {
        $this->wishes = new ArrayCollection();
        $this->fulfilledwishes = new ArrayCollection();
    }

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
     * @ORM\Column(name="fbId", type="string", length=255)
     */
    private $fbId;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="birthDate", type="date")
     */
    private $birthDate;

    /**
     * @var string
     *
     * @ORM\Column(name="profilePic", type="string", length=255)
     */
    private $profilePic;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fbId
     *
     * @param string $fbId
     * @return User
     */
    public function setFbId($fbId) {
        $this->fbId = $fbId;

        return $this;
    }

    /**
     * Get fbId
     *
     * @return string 
     */
    public function getFbId() {
        return $this->fbId;
    }

    /**
     * Get fbFbFriendListRequest
     *
     * @return string 
     */
    public function getFbFriendListRequest() {
        return '/' . $this->fbId . '?fields=friends.fields(id,first_name,birthday,last_name,installed)';
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string 
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string 
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Get displayName
     *
     * @return string 
     */
    public function getDisplayName() {
        return $this->firstName . ' ' . \substr($this->lastName, 0, 1) . '.';
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     * @return User
     */
    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime 
     */
    public function getBirthDate() {
        return $this->birthDate;
    }

    /**
     * Get age
     *
     * @return string
     */
    public function getAge() {
        if ($this->birthDate === null) {
            return null;
        }
        return $this->birthDate->diff(new \DateTime('now'))->y;
    }

    /**
     * Set profilePic
     *
     * @param string $profilePic
     * @return User
     */
    public function setProfilePic($profilePic) {
        $this->profilePic = $profilePic;

        return $this;
    }

    /**
     * Get profilePic
     *
     * @return string 
     */
    public function getProfilePic() {
        return $this->profilePic;
    }


    /**
     * Add wishes
     *
     * @param \EK\PDBundle\Entity\Wish $wishes
     * @return User
     */
    public function addWishe(\EK\PDBundle\Entity\Wish $wishes)
    {
        $this->wishes[] = $wishes;
    
        return $this;
    }

    /**
     * Remove wishes
     *
     * @param \EK\PDBundle\Entity\Wish $wishes
     */
    public function removeWishe(\EK\PDBundle\Entity\Wish $wishes)
    {
        $this->wishes->removeElement($wishes);
    }

    /**
     * Get wishes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWishes()
    {
        return $this->wishes;
    }

    /**
     * Add fulfilledwishes
     *
     * @param \EK\PDBundle\Entity\FulfillWish $fulfilledwishes
     * @return User
     */
    public function addFulfilledwishe(\EK\PDBundle\Entity\FulfillWish $fulfilledwishes)
    {
        $this->fulfilledwishes[] = $fulfilledwishes;
    
        return $this;
    }

    /**
     * Remove fulfilledwishes
     *
     * @param \EK\PDBundle\Entity\FulfillWish $fulfilledwishes
     */
    public function removeFulfilledwishe(\EK\PDBundle\Entity\FulfillWish $fulfilledwishes)
    {
        $this->fulfilledwishes->removeElement($fulfilledwishes);
    }

    /**
     * Get fulfilledwishes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFulfilledwishes()
    {
        return $this->fulfilledwishes;
    }
}