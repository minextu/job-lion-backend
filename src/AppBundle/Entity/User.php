<?php namespace JobLion\AppBundle\Entity;

/**
 * User database entity
 *
 * @Entity
 * @Table(name="users", indexes={@Index(name="email", columns={"email"})})
 */
class User
{
    /**
     * @var int
     *
     * @Id @Column(type="integer") @GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @Column(type="string", unique=true, length=100, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @Column(type="string", nullable=false)
     */
    private $firstName;

    /**
     * @var string
     *
     * @Column(type="string", nullable=false)
     */
    private $lastName;

    /**
     * Password hash
     * @var string
     *
     * @Column(type="string", length=100, nullable=false)
     */
    private $hash;

    /**
     * @var \DateTime
     *
     * @Column(type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var boolean
     *
     * @Column(type="boolean", nullable=false)
     */
    private $activated;

    /**
     * @var activationKey
     *
     * @Column(type="string", nullable=true)
     */
    private $activationCode;

    /**
    * @var JobCategory[] Job categories, this user added
    *
    * @OneToMany(targetEntity="JobCategory", mappedBy="user")
    **/
    private $addedJobCategories = null;

    /**
    * @var Report[] Experince reports this user submitted
    *
    * @OneToMany(targetEntity="JobLion\ExperienceReportBundle\Entity\ExperienceReport", mappedBy="user")
    **/
    private $submittedExperienceReports = null;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->activated = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string Password hash
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash Password hash
     * @return User
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return string boolean
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @param boolean $activated
     * @return User
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * @return string string
     */
    public function getActivationCode()
    {
        return $this->activationCode;
    }

    /**
     * @param boolean $activationCode
     * @return User
     */
    public function setActivationCode($activationCode)
    {
        $this->activationCode = $activationCode;

        return $this;
    }

    /**
      * Convert object to info array
      * @return array info array
      */
    public function toArray()
    {
        $arr = [
           "id" => $this->getId(),
           "email" => $this->getEmail(),
           "firstName" => $this->getFirstName(),
           "lastName" => $this->getLastName()
         ];

        return $arr;
    }
}
