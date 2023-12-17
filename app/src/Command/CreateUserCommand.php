<?php

namespace App\Command;

use App\Entity\User;
use App\Service\User\RegisterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create:user',
    description: 'Add a short description for your command',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private RegisterService $registerService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('password', InputArgument::REQUIRED, 'Password')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Add admin role');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->registerService->register(
                email: $input->getArgument('email'),
                plainPassword: $input->getArgument('password'),
                isAdmin: $input->getOption('admin'),
            );
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->success('User successfully created!');

        return Command::SUCCESS;
    }
}
