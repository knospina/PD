<?php

namespace EK\PDBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="EK\PDBundle\Entity\UserRepository")
 */
class User
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
     * @ORM\Column(name="fbId", type="string", length=255)
     */
    private $fbId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fbId
     *
     * @param string $fbId
     * @return User
     */
    public function setFbId($fbId)
    {
        $this->fbId = $fbId;
    
        return $this;
    }

    /**
     * Get fbId
     *
     * @return string 
     */
    public function getFbId()
    {
        return $this->fbId;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set birthDate
     *
     * @param \DateTime $birthDate
     * @return User
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    
        return $this;
    }

    /**
     * Get birthDate
     *
     * @return \DateTime 
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }
    
     /**
     * Set profilePic
     *
     * @param string $profilePic
     * @return User
     */
    public function setProfilePic($profilePic)
    {
        $this->profilePic = $profilePic;
    
        return $this;
    }

    /**
     * Get profilePic
     *
     * @return string 
     */
    public function getProfilePic()
    {
        return $this->profilePic;
    }
}