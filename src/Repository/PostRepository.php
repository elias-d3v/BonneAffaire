<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findAllSorted($sort, $categoryId = null, $dept = null, $q = null): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'SELECT * FROM post WHERE status = :status';
        $params = ['status' => 'validated'];

        if ($categoryId) {
            $sql .= ' AND category_id = :categoryId';
            $params['categoryId'] = $categoryId;
        }

        if ($dept) {
            $sql .= ' AND postal_code LIKE :dept';
            $params['dept'] = $dept . '%';
        }

        if ($q) {
            $sql .= ' AND (title LIKE :q OR description LIKE :q)';
            $params['q'] = '%'.$q.'%';
        }

        $sql .= $sort === 'price' ? ' ORDER BY price ASC' : ' ORDER BY published_at DESC';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery($params);

        return $result->fetchAllAssociative();
    }


    public function findByCategory($categoryId)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :cat')
            ->setParameter('cat', $categoryId)
            ->orderBy('p.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $term): array
    {
        $qb = $this->createQueryBuilder('p');

        $qb->andWhere('p.title LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->orderBy('p.publishedAt', 'DESC');

        $qb->andWhere('p.status = :status')
           ->setParameter('status', 'validated');

        return $qb->getQuery()->getResult();
    }
}
