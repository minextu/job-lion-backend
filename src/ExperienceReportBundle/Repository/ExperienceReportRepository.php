<?php namespace JobLion\ExperienceReportBundle\Repository;

use Doctrine\ORM\EntityRepository;
use JobLion\AppBundle\Entity\JobCategory;
use JobLion\ExperienceReportBundle\Entity\ExperienceReport;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ExperienceReportRepository extends EntityRepository
{
    /**
     * @param  mixed $jobCategoryIds
     * @param  integer $offset
     * @param  integer $limit
     * @return Paginator
     */
    public function findByJobCategories($jobCategoryIds=false, $offset=0, $limit=0)
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
        if (!empty($jobCategoryIds)) {
            $query
                ->innerJoin('e.jobCategories', 'cat')
                ->where('cat IN (:categories)')
                ->setParameter('categories', $jobCategoryIds);
        }

        return new Paginator($query, $fetchJoinCollection = !empty($jobCategoryIds));
    }
}
