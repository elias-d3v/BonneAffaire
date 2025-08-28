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

    public function findAllSorted(string $sort = 'date', ?int $categoryId = null, ?string $postalCode = null)
    {
        $qb = $this->createQueryBuilder('p');

        // Tri
        if ($sort === 'prix') {
            $qb->orderBy('p.price', 'ASC');
        } else {
            $qb->orderBy('p.publishedAt', 'DESC');
        }

        // Filtre catégorie
        if ($categoryId) {
            $qb->andWhere('p.category = :cat')
            ->setParameter('cat', $categoryId);
        }

        // Filtre département (2 premiers chiffres du code postal)
        if ($postalCode) {
            $qb->andWhere('p.postalCode LIKE :dept')
            ->setParameter('dept', $postalCode.'%');
        }

        // Status validé
        $qb->andWhere('p.status = :status')
        ->setParameter('status', 'validated');

        return $qb->getQuery()->getResult();
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
