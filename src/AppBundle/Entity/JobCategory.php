<?php namespace JobLion\AppBundle\Entity;

use JobLion\ExperienceReportBundle\Entity\ExperienceReport;

/**
 * Job Category database entity
 *
 * @Entity
 * @Table(name="jobCategories", indexes={@Index(name="name", columns={"name"})})
 */
class JobCategory
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
    private $name;

    /**
     * User that added this category to database
     * @var User
     *
     * @ManyToOne(targetEntity="User", inversedBy="addedJobCategories")
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @Column(type="datetime", nullable=false)
     */
    private $created;

    /**
    * @var ExperienceReport[] Experience reports for this category
    *
    * @ManyToMany(targetEntity="JobLion\ExperienceReportBundle\Entity\ExperienceReport", mappedBy="jobCategories")
    **/
    private $experienceReports = null;

    public function __construct()
    {
        $this->created = new \DateTime();
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return JobCategory
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return JobCategory
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
      * Convert object to info array
      * @param boolean $forAdmin
      * @return array       info array
      */
    public function toArray($forAdmin = false)
    {
        return [
          'id' => $this->getId(),
          'name' => $this->getName(),
          'user' => $this->getUser()->toArray($forAdmin),
          'created' => $this->getCreated()->format(\DateTime::ATOM)
        ];
    }
}
