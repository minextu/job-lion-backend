<?php namespace JobLion\ExperienceReportBundle\Repository;

use Doctrine\ORM\EntityRepository;
use JobLion\AppBundle\Entity\JobCategory;
use JobLion\ExperienceReportBundle\Entity\ExperienceReport;

class ExperienceReportRepository extends EntityRepository
{
    /**
     * @param  mixed $jobCategoryId
     * @param  integer $offset
     * @param  integer $limit
     * @return ExperienceReport[]
     */
    public function findByJobCategory($jobCategoryId=false, $offset=0, $limit=0)
    {
        if ($limit <= 0) {
            $limit = null;
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $query = $this->createQueryBuilder('e')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit);

        // only get reports by job category, if specified
        if (!empty($jobCategoryId)) {
            $query
                ->andWhere(':category MEMBER OF e.jobCategories')
                ->setParameter('category', $jobCategoryId);
        }

        return $query->getQuery()->getResult();
    }
}
