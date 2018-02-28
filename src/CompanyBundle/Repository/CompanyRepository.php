<?php namespace JobLion\CompanyBundle\Repository;

use Doctrine\ORM\EntityRepository;
use JobLion\Company\Entity\Company;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CompanyRepository extends EntityRepository
{
    /**
     * @param  integer $offset
     * @param  integer $limit
     * @return Paginator
     */
    public function findAll($offset=0, $limit=0)
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

        return new Paginator($query, $fetchJoinCollection = false);
    }
}
