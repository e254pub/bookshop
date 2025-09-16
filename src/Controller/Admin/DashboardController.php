<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\ContactMessage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(BookCrudController::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Bookshop Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Книги и категории');
        yield MenuItem::linkToCrud('Книги', 'fas fa-book', Book::class);
        yield MenuItem::linkToCrud('Категории', 'fas fa-folder', Category::class);

        yield MenuItem::section('Обратная связь');
        yield MenuItem::linkToCrud('Сообщения', 'fas fa-envelope', ContactMessage::class);
    }
}
