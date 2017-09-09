<?php

namespace AppBundle\Service;

use AppBundle\Entity\Token;
use AppBundle\Entity\User;
use \Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private const USER_ROLE_NAME = 'ROLE_USER';
    private const MANAGER_ROLE_NAME = 'ROLE_MANAGER';
    private const ADMIN_ROLE_NAME = 'ROLE_ADMIN';
    private const ACCOUNT_ACTIVATION_TOKEN_TYPE = 'ACTIVATION';
    private const PASSWORD_RESET_TOKEN_TYPE = 'PASSWORD';
    private const SECURITY_TOKEN_LENGTH = 50;

    private $doctrine;
    private $encoder;

    public function __construct(ManagerRegistry $doctrine, UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    public function registerNewUser(User $newUser)
    {
        if (!$this->isUserAlreadyExists($newUser)) {
            $token = $this->createSecurityToken();
            $this->prepareEntitiesForSavingInDatabase($newUser, $token);
            $this->saveEntitiesInDatabase($newUser, $token);
        }
    }

    public function activateUserAccount(int $id, string $token): bool
    {
        return true;
    }

    private function isUserAlreadyExists(User $user): bool
    {
        $repository = $this->doctrine->getManager()->getRepository(User::class);
        return $repository->findOneBy(['email' => $user->getEmail()]) !== null;
    }

    private function prepareEntitiesForSavingInDatabase(User $user, Token $token)
    {
        $this->encodeUserPassword($user);
        $user->setRole(self::USER_ROLE_NAME);
        $user->setIsActive(false);
        $token->setUser($user);
    }

    private function encodeUserPassword(User $user)
    {
        $encodedPassword = $this->encoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($encodedPassword);
    }

    private function saveEntitiesInDatabase(User $user, TOken $token)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($user);
        $manager->flush();
        $manager->persist($token);
        $manager->flush();
    }

    private function createSecurityToken(): Token
    {
        $token = new Token();
        $tokenValue = bin2hex(openssl_random_pseudo_bytes(self::SECURITY_TOKEN_LENGTH));
        $token->setToken($tokenValue);
        $token->setType(self::ACCOUNT_ACTIVATION_TOKEN_TYPE);
        return $token;
    }
}