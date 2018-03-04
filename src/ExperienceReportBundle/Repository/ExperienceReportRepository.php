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

        $query = $this->createQueryBuilder('report')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit);

        // only get reports by job category, if specified
        if (!empty($jobCategoryIds)) {
            $query
                ->join('report.jobCategories', 'cat')
                ->where('cat IN (:categories)')
                ->groupBy('report.id')
                ->having('COUNT(cat.name) >= :categoryCount')
                ->setParameter('categories', $jobCategoryIds)
                ->setParameter('categoryCount', count($jobCategoryIds));
        }

        return new Paginator($query, $fetchJoinCollection = !empty($jobCategoryIds));
    }
}
