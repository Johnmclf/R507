<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * @return Contact[] Returns an array of Contact objects
     */
    public function findPaginatedByStatus(int $page, int $limit, ?string $status = null): array
    {
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('c');

        if ($status && $status !== 'all') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        return $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(?string $status = null): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('count(c.id)');

        if ($status && $status !== 'all') {
            $qb->andWhere('c.status = :status')
                ->setParameter('status', $status);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Contact[] Returns an array of Contact objects
     */
    public function search(string $search): array
    {
        $qb = $this->createQueryBuilder('c');
        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.firstName', ':search'),
                    $qb->expr()->like('c.name', ':search'),
                ),
            )
            ->setParameter('search', '%'.$search.'%')
            ->getQuery()
            ->getResult()
            ;
    }
}
