<?php

declare(strict_types=1);

namespace App\DataFixture;

use App\Entity\User;
use App\DataFixture\Factory\UserFactory;
use App\Service\User\RegisterService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const REFERENCES_ADMIN = [
        'REFERENCE_ADMIN_1',
        'REFERENCE_ADMIN_2',
    ];

    public const REFERENCES_USER = [
        'REFERENCE_USER_1',
        'REFERENCE_USER_2',
        'REFERENCE_USER_3',
        'REFERENCE_USER_4',
    ];

    public function __construct(
        private RegisterService $registerService,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $userFactory = UserFactory::new();

        // Admin users
        $adminUsersCount = count($this::REFERENCES_ADMIN);
        $adminUsers = $userFactory->many($adminUsersCount)
            ->create(
                attributes: [
                    'roles' => [User::ROLE_ADMIN],
                ],
            );

        foreach ($adminUsers as $key => $user) {
            $this->addReference(self::REFERENCES_ADMIN[$key], $user->object());
        }

        // Normal users
        $normalUsersCount = count($this::REFERENCES_USER);
        $normalUsers = $userFactory->many($normalUsersCount)
            ->create();

        foreach ($normalUsers as $key => $user) {
            $this->addReference(self::REFERENCES_USER[$key], $user->object());
        }
    }
}
