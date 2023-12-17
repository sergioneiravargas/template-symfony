<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Service\User\RegisterService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private RegisterService $registerService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = $this->loadAdminUser($manager);
    }

    public function loadAdminUser(ObjectManager $manager): User
    {
        $email = 'admin@email.com';
        $plainPassword = 'password';

        try {
            $user = $this->registerService->createUser(
                email: $email,
                plainPassword: $plainPassword,
                isAdmin: true,
            );
            $manager->persist($user);
            $manager->flush();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $user;
    }
}
