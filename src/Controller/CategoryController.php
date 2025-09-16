<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(
        CategoryRepository $categoryRepository,
        BookRepository $bookRepository
    ): Response {
        $topLevelCategories = $categoryRepository->findTopLevelCategories();

        $categoriesWithCount = [];
        foreach ($topLevelCategories as $category) {
            $count = $bookRepository->countByCategoryRecursive($category);
            $categoriesWithCount[] = [
                'category' => $category,
                'book_count' => $count,
            ];
        }

        usort($categoriesWithCount, fn($a, $b) => $b['book_count'] <=> $a['book_count']);

        return $this->render('category/index.html.twig', [
            'categories_with_count' => $categoriesWithCount,
        ]);
    }

    #[Route('/{id}', name: 'category_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(
        int $id,
        Request $request,
        CategoryRepository $categoryRepository,
        BookRepository $bookRepository,
        PaginatorInterface $paginator
    ): Response {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw $this->createNotFoundException('Категория не найдена');
        }

        $children = $category->getChildren();
        if ($children->count() > 0) {
            return $this->render('category/children.html.twig', [
                'parent' => $category,
                'categories' => $children,
            ]);
        }

        // Получаем QueryBuilder с фильтрами из репозитория
        $filters = [
            'search' => $request->query->get('search'),
            'author' => $request->query->get('author'),
            'status' => $request->query->get('status'),
        ];

        $query = $bookRepository->findWithFilters($category, $filters);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            (int)$_ENV['APP_ITEMS_PER_PAGE'] ?? 10
        );

        return $this->render('category/books.html.twig', [
            'category' => $category,
            'pagination' => $pagination,
            'search' => $filters['search'],
            'author' => $filters['author'],
            'status' => $filters['status'],
        ]);
    }
}
