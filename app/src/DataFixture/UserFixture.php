<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\DataFixture\Factory\UserFactory;
use App\Entity\User;
use App\Service\User\RegisterService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const REFERENCE_ADMIN_1 = 'REFERENCE_ADMIN_1';

    public const REFERENCE_USER_1 = 'REFERENCE_USER_1';
    public const REFERENCE_USER_2 = 'REFERENCE_USER_2';

    public function __construct(
        private RegisterService $registerService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $defaultPassword = 'password';

        $admin = UserFactory::new()
            ->withAttributes([
                'email' => 'admin@email.com',
                'plainPassword' => $defaultPassword,
            ])
            ->promoteRole(User::ROLE_ADMIN)
            ->create();
        $this->addReference(self::REFERENCE_ADMIN_1, $admin->object());

        // Normal users
        $user1 = UserFactory::new()
            ->withAttributes([
                'email' => 'user1@email.com',
                'plainPassword' => $defaultPassword,
            ])
            ->create();
        $this->addReference(self::REFERENCE_USER_1, $user1->object());

        $user2 = UserFactory::new()
            ->withAttributes([
                'email' => 'user2@email.com',
                'plainPassword' => $defaultPassword,
            ])
            ->create();
        $this->addReference(self::REFERENCE_USER_2, $user2->object());
    }
}
