<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Book;
use App\Entity\Category;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:parse-books',
    description: 'json books data parse'
)]
class ParseBooksCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $client,
        private BookRepository $bookRepository,
        private CategoryRepository $categoryRepository,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sourceFile = $_ENV['PARSER_SOURCE_FILE'] ?? $this->projectDir . '/books.json';
        if (!file_exists($sourceFile)) {
            $io->error("Файл не найден: $sourceFile");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($sourceFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Некорректный JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        // Собрать все уникальные категории из JSON
        $categoryNamesFromJson = [];
        foreach ($data as $item) {
            foreach ($item['categories'] as $catName) {
                $clean = trim($catName);
                if ($clean !== '') {
                    $normalized = $this->normalizeCategoryName($clean);
                    $categoryNamesFromJson[$normalized] = $clean; // сохраняем оригинальное написание для вывода
                }
            }
        }

        // Загрузить все существующие категории из БД
        $existingCategoriesInDb = $this->categoryRepository->findAll();
        $existingByNormalized = [];

        foreach ($existingCategoriesInDb as $cat) {
            $normalized = $this->normalizeCategoryName($cat->getName());
            $existingByNormalized[$normalized] = $cat;
        }

        // Создать отсутствующие категории
        $newCategories = [];
        foreach ($categoryNamesFromJson as $norm => $original) {
            if (!isset($existingByNormalized[$norm])) {
                $category = new Category();
                $category->setName(ucwords(strtolower($original))); // форматирование: "jAvA" → "Java"
                $this->em->persist($category);
                $existingByNormalized[$norm] = $category;
                $newCategories[] = ucwords(strtolower($original));
            }
        }

        if ($newCategories) {
            $io->note('Созданы категории: ' . implode(', ', array_unique($newCategories)));
        }

        // Получить или создать Новинки
        $defaultNorm = $this->normalizeCategoryName('Новинки');
        $defaultCategory = $existingByNormalized[$defaultNorm] ?? null;

        if (!$defaultCategory) {
            $defaultCategory = new Category();
            $defaultCategory->setName('Новинки');
            $this->em->persist($defaultCategory);
            $existingByNormalized[$defaultNorm] = $defaultCategory;
            $io->note('Создана категория по умолчанию: "Новинки"');
        }

        // Парсим книги
        $processed = 0;
        $newBooks = 0;

        foreach ($data as $item) {
            if (empty($item['title'])) continue;

            $isbn = $item['isbn'] ?? null;
            $book = $isbn ? $this->bookRepository->findByIsbn($isbn) : null;

            if (!$book) {
                $book = new Book();
                $newBooks++;
            }

            // Заполняем данные книги
            $book->setTitle($item['title'])
                ->setIsbn($isbn)
                ->setPageCount($item['pageCount'] ?? null)
                ->setStatus($item['status'] ?? 'PUBLISH')
                ->setAuthors($item['authors'] ?? []);

            // Дата публикации
            if (isset($item['publishedDate']['$date'])) {
                try {
                    $book->setPublishedDate(new \DateTimeImmutable($item['publishedDate']['$date']));
                } catch (\Exception) {}
            }

            // Изображение
            if (!empty($item['thumbnailUrl'])) {
                $localPath = $this->downloadImage($item['thumbnailUrl']);
                if ($localPath) {
                    $book->setImagePath($localPath);
                }
            }

            // Привязка категорий
            $book->getCategories()->clear();
            $usedNormalizedNames = [];

            foreach ($item['categories'] as $catName) {
                $clean = trim($catName);
                if (empty($clean)) continue;

                $normName = $this->normalizeCategoryName($clean);
                if (in_array($normName, $usedNormalizedNames, true)) {
                    continue; // избегаем дублей в одной книге
                }

                $category = $existingByNormalized[$normName] ?? $defaultCategory;
                $book->addCategory($category);
                $usedNormalizedNames[] = $normName;
            }

            // Если нет категорий добавляем Новинки
            if (empty($usedNormalizedNames)) {
                $book->addCategory($defaultCategory);
            }

            $this->em->persist($book);
            $processed++;
        }

        try {
            $this->em->flush();
            $io->success([
                "Парсинг завершён.",
                "Обработано книг: $processed",
                "Новых книг: $newBooks"
            ]);
        } catch (\Exception $e) {
            $io->error('Ошибка при сохранении: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function normalizeCategoryName(string $name): string
    {
        $name = strtolower(trim($name));
        // объединяет множественные пробелы/дефисы
        $name = preg_replace('/[-_\s]+/', ' ', $name);
        return trim($name);
    }

    /**
     * Скачивает изображение и сохраняет локально
     */
    private function downloadImage(string $url): ?string
    {
        try {
            $response = $this->client->request('GET', $url);
            $content = $response->getContent();

            $filename = 'uploads/thumbnails/' . basename(parse_url($url, PHP_URL_PATH));
            $path = $this->projectDir . '/public/' . $filename;

            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($path, $content);
            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
