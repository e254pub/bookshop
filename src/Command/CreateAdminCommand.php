<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand('app:create-admin', 'Создаёт админа с email и паролем')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = new User();
        $user->setEmail('admin@example.com')
            ->setRoles(['ROLE_ADMIN']);

        $plaintextPassword = 'admin'; // можно передавать как аргумент
        $hashed = $this->hasher->hashPassword($user, $plaintextPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $io->success('Администратор создан: admin@example.com / admin');
        return Command::SUCCESS;
    }
}