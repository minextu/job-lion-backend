<?php namespace JobLion\CommentBundle\Entity;

/**
 * Comment database entity
 *
 * @Entity(repositoryClass="JobLion\CommentBundle\Repository\CommentRepository")
 * @Table(name="comments")
 */
class Comment
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
    private $text;

    /**
     * User that wrote this comment
     * @var User
     *
     * @ManyToOne(targetEntity="JobLion\AppBundle\Entity\User", inversedBy="writtenComments")
     */
    private $user;

    /**
     * Experience report this comment was written for
     * @var ExperienceReport
     *
     * @ManyToOne(targetEntity="JobLion\ExperienceReportBundle\Entity\ExperienceReport", inversedBy="comments")
     */
    private $experienceReport = null;

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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Comment
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
     * @return Comment
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return ExperienceReport
     */
    public function getExperienceReport()
    {
        return $this->experienceReport;
    }

    /**
     * @param ExperienceReport $experienceReport
     * @return Comment
     */
    public function setExperienceReport($experienceReport)
    {
        $this->experienceReport = $experienceReport;
        return $this;
    }

    /**
      * Convert object to info array
      * @return array info array
      */
    public function toArray()
    {
        return [
          "id" => $this->getId(),
          "text" => $this->getText(),
          "experienceReportId" => $this->getExperienceReport()->getId(),
          "user" => $this->getUser()->toArray(),
          "created" => $this->getCreated()->format(\DateTime::ATOM)
      ];
    }
}
