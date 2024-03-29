<?php namespace JobLion\AppBundle\Entity;

use JobLion\ExperienceReportBundle\Entity\ExperienceReport;
use JobLion\CommentBundle\Entity\Comment;
use JobLion\CompanyBundle\Entity\Company;

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
     * @var string
     *
     * @Column(type="boolean", nullable=false)
     */
    private $isAdmin;

    /**
    * @var JobCategory[] Job categories, this user added
    *
    * @OneToMany(targetEntity="JobCategory", mappedBy="user")
    **/
    private $addedJobCategories = null;

    /**
    * @var ExperienceReport[] Experience reports this user submitted
    *
    * @OneToMany(targetEntity="JobLion\ExperienceReportBundle\Entity\ExperienceReport", mappedBy="user")
    **/
    private $submittedExperienceReports = null;

    /**
    * @var Company[] Companies this user added
    *
    * @OneToMany(targetEntity="JobLion\CompanyBundle\Entity\Company", mappedBy="user")
    **/
    private $addedCompanies = null;

    /**
    * @var Comment[] Comments that this user has written
    *
    * @OneToMany(targetEntity="JobLion\CommentBundle\Entity\Comment", mappedBy="user")
    **/
    private $writtenComments = null;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->activated = false;
        $this->isAdmin = false;
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
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * @param boolean $isAdmin
     * @return User
     */
    public function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * @return string avatar url for this email
     */
    public function getAvatar()
    {
        $size = 40;
        $gravUrl = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($this->getEmail()))) . "&s=" . $size;
        return $gravUrl;
    }

    /**
      * Convert object to info array
      * @param boolean $forAdmin
      * @return array info array
      */
    public function toArray($forAdmin=false)
    {
        $arr = [
           "id" => $this->getId(),
           "avatar" => $this->getAvatar(),
           "firstName" => $this->getFirstName(),
           "lastName" => $this->getLastName(),
           "isAdmin" => $this->getIsAdmin()
         ];

        if ($forAdmin) {
            $arr['email'] = $this->getEmail();
        }

        return $arr;
    }
}
