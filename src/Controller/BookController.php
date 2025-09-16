<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/book')]
class BookController extends AbstractController
{
    #[Route('/{id}', name: 'book_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Book $book, BookRepository $bookRepository): Response
    {
        $relatedBooks = $bookRepository->findRelatedBooks($book, 4);

        return $this->render('book/show.html.twig', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }
}
