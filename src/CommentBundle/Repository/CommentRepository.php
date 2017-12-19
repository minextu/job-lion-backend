<?php namespace JobLion\CommentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use JobLion\ExperienceReportBundle\Entity\ExperienceReport;
use JobLion\CommentBundle\Entity\Comment;

class CommentRepository extends EntityRepository
{
    /**
     * @param  integer $experienceReportId
     * @param  integer $offset
     * @param  integer $limit
     * @return Comment[]
     */
    public function findByExperienceReport($experienceReportId, $offset=0, $limit=0)
    {
        if ($limit <= 0) {
            $limit = null;
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $query = $this->createQueryBuilder('c')
                      ->where('c.experienceReport = :report ')
                      ->setParameter('report', $experienceReportId)
                      ->setFirstResult($offset)
                      ->setMaxResults($limit);


        return $query->getQuery()->getResult();
    }
}
