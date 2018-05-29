<?php namespace JobLion\ExperienceReportBundle\Entity;

use JobLion\AppBundle\Entity\User;
use JobLion\AppBundle\Entity\JobCategory;
use JobLion\CommentBundle\Entity\Comment;
use JobLion\CompanyBundle\Entity\Company;

/**
 * Experience Report database entity
 *
 * @Entity(repositoryClass="JobLion\ExperienceReportBundle\Repository\ExperienceReportRepository")
 * @Table(name="experienceReports")
 */
class ExperienceReport
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
     * @Column(type="string", nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @Column(type="string", nullable=false)
     */
    private $text;

    /**
     * User that added this category to database
     * @var User
     *
     * @ManyToOne(targetEntity="JobLion\AppBundle\Entity\User", inversedBy="submittedExperienceReports")
     */
    private $user;

    /**
     * Job Categories for this report
     * @var JobCategory[]
     *
     * @ManyToMany(targetEntity="JobLion\AppBundle\Entity\JobCategory", inversedBy="experienceReports")
     */
    private $jobCategories = null;

    /**
     * Comments for this report
     * @var Comment[]
     *
     * @OneToMany(targetEntity="JobLion\CommentBundle\Entity\Comment", mappedBy="experienceReport")
     */
    private $comments = null;

    /**
     * Company this report is associated with
     * @var Company
     *
     * @ManyToOne(targetEntity="JobLion\CompanyBundle\Entity\Company", inversedBy="experienceReports")
     */
    private $company;

    /**
     * @var \DateTime
     *
     * @Column(type="datetime", nullable=false)
     */
    private $created;

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return ExperienceReport
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return ExperienceReport
     */
    public function setText($text)
    {
        $this->text = $text;
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
     * @return ExperienceReport
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return JobCategory[]
     */
    public function getJobCategories()
    {
        return $this->jobCategories;
    }

    /**
     * @param JobCategory[] $jobCategories
     * @return ExperienceReport
     */
    public function setJobCategories($jobCategories)
    {
        $this->jobCategories = $jobCategories;
        return $this;
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Company $company
     * @return ExperienceReport
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
      * Convert object to info array
      * @param boolean $forAdmin
      * @return array info array
      */
    public function toArray($forAdmin)
    {
        $jobCategories = $this->getJobCategories();
        $categoryArray = [];
        foreach ($jobCategories as $category) {
            $categoryArray[] = $category->toArray($forAdmin);
        }

        return [
          "id" => $this->getId(),
          "title" => $this->getTitle(),
          "text" => $this->getText(),
          "jobCategories" => $categoryArray,
          "company" => $this->getCompany() ? $this->getCompany()->toArray($forAdmin) : null,
          "user" => $this->getUser()->toArray($forAdmin),
          "created" => $this->getCreated()->format(\DateTime::ATOM)
      ];
    }
}
