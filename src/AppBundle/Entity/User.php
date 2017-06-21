<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="fos_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="email", column=@ORM\Column(type="string", name="email", length=255, unique=false, nullable=true)),
 *      @ORM\AttributeOverride(name="emailCanonical", column=@ORM\Column(type="string", name="email_canonical", length=255, unique=false, nullable=true))
 *
 * })
 */
class User extends BaseUser
{

	public function __construct() {
		parent::__construct();
		$this->setFirstName("");
		$this->setLastName("");
		$this->setStatus(1);
		$this->dateEntree = new \DateTime();

	}


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="Please enter your first name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The first name is too short.",
     *     maxMessage="The first name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $firstName;

    /**
     * @var string
     * @ORM\Column(name="last_name", type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your last name.", groups={"Registration", "Profile"})
     * @Assert\Length(
     *     min=2,
     *     max=255,
     *     minMessage="The last name is too short.",
     *     maxMessage="The last name is too long.",
     *     groups={"Registration", "Profile"}
     * )
     */
    private $lastName;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL", name="superior1_id", referencedColumnName="id", nullable=true)
     */
    private $superiorLvl1;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(onDelete="SET NULL", name="superior2_id", referencedColumnName="id", nullable=true)
     */
    private $superiorLvl2;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_number", type="string", nullable=true)
     */
    private $registrationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="site", type="string", nullable=true)
     */
    private $site;

     /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_entree", type="date", nullable=true)
     */
    private $dateEntree;

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
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

     /**
     * Get lastNameFirstName
     *
     * @return string
     */
    public function getLastNameFirstName()
    {
        return $this->lastName." ".$this->firstName;
    }

    /**
     * Set dateOfBirth
     *
     * @param \DateTime $dateOfBirth
     *
     * @return User
     */
    public function setDateOfBirth($dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * Get dateOfBirth
     *
     * @return \DateTime
     */
    public function getDateOfBirth ()
    {
        return $this->dateOfBirth;
    }

    /**
     * Set superiorLvl1
     *
     * @param User $superiorLvl1
     *
     * @return User
     */
    public function setSuperiorLvl1($superiorLvl1)
    {
        $this->superiorLvl1 = $superiorLvl1;

        return $this;
    }

    /**
     * Get superiorLvl1
     *
     * @return User
     */
    public function getSuperiorLvl1()
    {
        return $this->superiorLvl1;
    }

    /**
     * Set superiorLvl2
     *
     * @param User $superiorLvl2
     *
     * @return User
     */
    public function setSuperiorLvl2($superiorLvl2)
    {
        $this->superiorLvl2 = $superiorLvl2;

        return $this;
    }

    /**
     * Get superiorLvl2
     *
     * @return User
     */
    public function getSuperiorLvl2()
    {
        return $this->superiorLvl2;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return User
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get registrationNumber
     *
     * @return string
     */
    public function getRegistrationNumber()
    {
        return $this->registrationNumber;
    }

    /**
     * Set registrationNumber
     *
     * @param string $registrationNumber
     *
     * @return User
     */
    public function setRegistrationNumber($registrationNumber = null)
    {
        $this->registrationNumber = $registrationNumber;

        return $this;
    }

     /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set site
     *
     * @param string $registrationNumber
     *
     * @return User
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

     /**
     * Set dateEntree
     *
     * @param \DateTime $dateEntree
     *
     * @return User
     */
    public function setDateEntree($dateEntree)
    {
        $this->dateEntree = $dateEntree;

        return $this;
    }

    /**
     * Get dateEntree
     *
     * @return \DateTime
     */
    public function getDateEntree ()
    {
        return $this->dateEntree;
    }


}
