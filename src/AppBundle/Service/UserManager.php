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
    private $mailManager;

    public function __construct(ManagerRegistry $doctrine, MailManager $mailManager, UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->mailManager = $mailManager;
    }

    public function registerNewUser(User $newUser)
    {
        if (!$this->isUserAlreadyExists($newUser)) {
            $token = $this->createSecurityToken();
            $this->prepareEntitiesForSavingInDatabase($newUser, $token);
            $this->saveEntitiesToDatabase($newUser, $token);
            $this->mailManager->sendActivationEmail($newUser, $token);
        }
    }

    public function isUserAccountActivationSucceed(int $id, string $tokenValue): bool
    {
        $token = $this->getActivationToken($id);
        if ($token === null) {
            return false;
        }
        if (!$token->isValid($tokenValue)) {
            return false;
        }
        $this->activateUserAcount($token);
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

    private function saveEntitiesToDatabase(User $user, TOken $token)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($user);
        $manager->flush();
        $manager->persist($token);
        $manager->flush();
    }

    private function getActivationToken(int $id):? Token
    {
        $repository = $this->doctrine->getManager()->getRepository(Token::class);
        return $repository->findOneBy([
            'id' => $id,
            'type' => self::ACCOUNT_ACTIVATION_TOKEN_TYPE
        ]);
    }

    private function activateUserAcount(Token $token)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($token);
        $user = $token->getUser();
        $user->setIsActive(true);
        $manager->persist($user);
        $manager->remove($token);
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
