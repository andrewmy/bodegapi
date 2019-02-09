<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @codeCoverageIgnore
 */
class Users extends Fixture
{
    public const API_LOGIN = 'api_user';
    public const API_PASSWORD = 'api_ipa';

    public const ADMIN_LOGIN = 'admin_user';
    public const ADMIN_PASSWORD = 'admin_nidma';

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser
            ->setUsername(self::ADMIN_LOGIN)
            ->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $adminUser, self::ADMIN_PASSWORD
                )
            )
            ->setRoles([User::ROLE_ADMIN]);

        $apiUser = (new User())
            ->setUsername(self::API_LOGIN)
            ->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $adminUser, self::API_PASSWORD
                )
            )
            ->setRoles([User::ROLE_USER]);

        $manager->persist($adminUser);
        $manager->persist($apiUser);
        $manager->flush();
    }
}
