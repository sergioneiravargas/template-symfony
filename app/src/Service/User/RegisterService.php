<?php

declare(strict_types=1);

namespace App\Service\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterService
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
    ) {
    }

    public function createUser(
        string $email,
        string $plainPassword,
        bool $isAdmin = false,
    ): User {
        $user = new User();
        $user
            ->setEmail($email)
            ->setPlainPassword($plainPassword);

        if ($isAdmin) {
            $user->setRoles([User::ROLE_ADMIN]);
        }

        $errors = $this->validator->validate(
            $user,
            null,
            [
                User::GROUP_REGISTER,
            ],
        );
        if (count($errors) > 0) {
            throw new \Exception((string) $errors);
        }

        $password = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user
            ->setPassword($password)
            ->eraseCredentials();

        return $user;
    }

    public function register(
        string $email,
        string $plainPassword,
        bool $isAdmin = false,
    ): User {
        $user = $this->createUser(
            email: $email,
            plainPassword: $plainPassword,
            isAdmin: $isAdmin,
        );

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
