<?php namespace JobLion\CompanyBundle\Entity;

/**
 * Job Category database entity
 *
 * @Entity(repositoryClass="JobLion\CompanyBundle\Repository\CompanyRepository")
 * @Table(name="companies", indexes={@Index(name="title", columns={"title"})})
 */
class Company
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
    private $title;

    /**
     * User that added this company to database
     * @var User
     *
     * @ManyToOne(targetEntity="JobLion\AppBundle\Entity\User", inversedBy="addedCompanies")
     */
    private $user;

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
     * @return Company
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return Company
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
      * Convert object to info array
      * @return array       info array
      */
    public function toArray()
    {
        return [
          'id' => $this->getId(),
          'title' => $this->getTitle(),
          'user' => $this->getUser()->toArray(),
          'created' => $this->getCreated()->format(\DateTime::ATOM)
        ];
    }
}
