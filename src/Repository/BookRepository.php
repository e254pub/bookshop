<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Поиск книг по категории с фильтрацией
     */
    public function findWithFilters(Category $category, array $filters = []): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->innerJoin('b.categories', 'c')
            ->where('c.id = :cat_id')
            ->setParameter('cat_id', $category->getId());

        if (!empty($filters['search'])) {
            $qb->andWhere('b.title LIKE :title')
                ->setParameter('title', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['author'])) {
            $jsonValue = '"' . addcslashes($filters['author'], '"\\') . '"';
            $qb->andWhere('b.authors LIKE :author_pattern')
                ->setParameter('author_pattern', '%' . $jsonValue . '%');
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('b.status = :status')
                ->setParameter('status', $filters['status']);
        }

        return $qb;
    }

    /**
     * Подсчёт книг в категории и всех её подкатегориях
     */
    public function countByCategoryRecursive(Category $category): int
    {
        $allIds = $this->getAllCategoryIdsRecursive($category);
        if (empty($allIds)) {
            return 0;
        }

        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(DISTINCT b.id)')
            ->innerJoin('b.categories', 'c')
            ->where('c.id IN (:ids)')
            ->setParameter('ids', $allIds)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Найти книги из той же категории (рекомендации)
     */
    public function findRelatedBooks(Book $book, ?int $limit = 4): array
    {
        $categories = $book->getCategories();
        if ($categories->isEmpty()) {
            return [];
        }

        $firstCategory = $categories->first();

        return $this->createQueryBuilder('b')
            ->innerJoin('b.categories', 'c')
            ->where('c = :category')
            ->andWhere('b != :current_book')
            ->setParameter('category', $firstCategory)
            ->setParameter('current_book', $book)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    private function getAllCategoryIdsRecursive(Category $category): array
    {
        $ids = [$category->getId()];
        $stack = [$category];

        while (!empty($stack)) {
            $parent = array_pop($stack);
            foreach ($parent->getChildren() as $child) {
                $ids[] = $child->getId();
                $stack[] = $child;
            }
        }

        return $ids;
    }
}
