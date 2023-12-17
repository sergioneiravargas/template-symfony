<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $email = 'admin@email.com';
        $plainPassword = 'password';

        $user = new User();
        $password = $this->passwordHasher->hashPassword($user, $plainPassword);

        $user
            ->setEmail($email)
            ->setPassword($password)
            ->setRoles([User::ROLE_ADMIN]);

        $manager->persist($user);

        $manager->flush();
    }
}
