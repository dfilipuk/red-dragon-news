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

    /**
     * UserManager constructor.
     * @param ManagerRegistry $doctrine
     * @param MailManager $mailManager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(ManagerRegistry $doctrine, MailManager $mailManager, UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->mailManager = $mailManager;
    }

    /**
     * @param User $newUser
     */
    public function registerNewUser(User $newUser): void
    {
        if (!$this->isUserAlreadyExists($newUser)) {
            $token = $this->createSecurityTokenForAccountActivation();
            $this->prepareEntitiesForSavingInDatabase($newUser, $token);
            $this->saveEntitiesToDatabase($newUser, $token);
            $this->mailManager->sendActivationEmail($newUser, $token);
        }
    }

    /**
     * @param int $tokenId
     * @param User $userWithPassword
     */
    public function resetPasswordForUser(int $tokenId, User $userWithPassword): void
    {
        $token = $this->getResetPasswordToken($tokenId);
        if ($token !== null) {
            $user = $token->getUser();
            $this->encodeUserPassword($user, $userWithPassword->getPlainPassword());
            $this->updateUserInformation($user);
            $this->removeResetPasswordTokenFromDatabase($token);
        }
    }

    /**
     * @param User $userWithEmail
     */
    public function setResetPasswordTokenForUser(User $userWithEmail): void
    {
        $user = $this->getUserByEmail($userWithEmail->getEmail());
        if ($user !== null) {
            $token = $this->createSecurityTokenForPasswordReset();
            $this->saveResetTokenToDatabase($user, $token);
            $this->mailManager->sendResetPasswordEmail($user, $token);
        }
    }

    /**
     * @param int $id
     * @param string $tokenValue
     * @return bool
     */
    public function isResetPasswordTokenValid(int $id, string $tokenValue): bool
    {
        $token = $this->getResetPasswordToken($id);
        if ($token === null) {
            return false;
        }
        if (!$token->isValid($tokenValue)) {
            return false;
        }
        if ($token->isAlive()) {
            return true;
        } else {
            $this->removeResetPasswordTokenFromDatabase($token);
            return false;
        }
    }

    /**
     * @param int $id
     * @param string $tokenValue
     * @return bool
     */
    public function isUserAccountActivationSucceed(int $id, string $tokenValue): bool
    {
        $token = $this->getActivationToken($id);
        if ($token === null) {
            return false;
        }
        if (!$token->isValid($tokenValue)) {
            return false;
        }
        $this->activateUserAccount($token);
        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isUserAlreadyExists(User $user): bool
    {
        if ($this->getUserByEmail($user->getEmail()) === null) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $userEmail
     * @return User|null
     */
    private function getUserByEmail(string $userEmail): ?User
    {
        $repository = $this->doctrine->getManager()->getRepository(User::class);
        return $repository->findOneBy(['email' => $userEmail]);
    }

    /**
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        $repository = $this->doctrine->getManager()->getRepository(User::class);
        return $repository->findOneBy(['id' => $id]);
    }


    /**
     * @param User $user
     * @param Token $token
     */
    private function prepareEntitiesForSavingInDatabase(User $user, Token $token): void
    {
        $this->encodeUserPassword($user, $user->getPlainPassword());
        $user->setRole(self::USER_ROLE_NAME);
        $user->setIsActive(false);
        $token->setUser($user);
    }


    /**
     * @param User $user
     * @param string $plainPassword
     */
    private function encodeUserPassword(User $user, string $plainPassword): void
    {
        $encodedPassword = $this->encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encodedPassword);
    }

    /**
     * @param User $user
     */
    private function updateUserInformation(User $user): void
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @param User $user
     * @param Token $token
     */
    private function saveEntitiesToDatabase(User $user, Token $token): void
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($user);
        $manager->flush();
        $manager->persist($token);
        $manager->flush();
    }

    /**
     * @param User $user
     * @param Token $token
     */
    private function saveResetTokenToDatabase(User $user, Token $token): void
    {
        $token->setUser($user);
        $manager = $this->doctrine->getManager();
        $manager->persist($token);
        $manager->flush();
    }

    /**
     * @param Token $token
     */
    private function activateUserAccount(Token $token): void
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($token);
        $user = $token->getUser();
        $user->setIsActive(true);
        $manager->persist($user);
        $manager->remove($token);
        $manager->flush();
    }

    /**
     * @return Token
     */
    private function createSecurityTokenForAccountActivation(): Token
    {
        $token = new Token();
        $tokenValue = bin2hex(openssl_random_pseudo_bytes(self::SECURITY_TOKEN_LENGTH));
        $token->setToken($tokenValue);
        $token->setType(self::ACCOUNT_ACTIVATION_TOKEN_TYPE);
        return $token;
    }

    /**
     * @return Token
     */
    private function createSecurityTokenForPasswordReset(): Token
    {
        $token = new Token();
        $tokenValue = bin2hex(openssl_random_pseudo_bytes(self::SECURITY_TOKEN_LENGTH));
        $token->setToken($tokenValue);
        $token->setType(self::PASSWORD_RESET_TOKEN_TYPE);
        $token->setDate(new \DateTime('now'));
        return $token;
    }

    /**
     * @param int $id
     * @return Token|null
     */
    private function getActivationToken(int $id): ?Token
    {
        $repository = $this->doctrine->getManager()->getRepository(Token::class);
        return $repository->findOneBy([
            'id' => $id,
            'type' => self::ACCOUNT_ACTIVATION_TOKEN_TYPE
        ]);
    }

    /**
     * @param int $id
     * @return Token|null
     */
    private function getResetPasswordToken(int $id): ?Token
    {
        $repository = $this->doctrine->getManager()->getRepository(Token::class);
        return $repository->findOneBy([
            'id' => $id,
            'type' => self::PASSWORD_RESET_TOKEN_TYPE,
        ]);
    }

    /**
     * @param Token $token
     */
    private function removeResetPasswordTokenFromDatabase(Token $token): void
    {
        $manager = $this->doctrine->getManager();
        $manager->remove($token);
        $manager->flush();
    }

    /**
     * @param User $user
     * @param \Symfony\Component\Form\Form $form
     * @param string $originalPass
     */
    public function editUser(User $user, \Symfony\Component\Form\Form $form, string $originalPass): void
    {
        $manager = $this->doctrine->getManager();
        $plainPassword = $form->get('plainPassword')->getData();
        if (!empty($plainPassword)) {
            $encodedPassword = $this->encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encodedPassword);
        } else {
            $user->setPassword($originalPass);
        }
        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @param int $id
     */
    public function deleteUserById(int $id): void
    {
        $manager = $this->doctrine->getManager();
        $user = $this->getUserById($id);
        $role = $user->getRole();
        if ($role === self::MANAGER_ROLE_NAME || $role === self::ADMIN_ROLE_NAME) {
        }
        if ($user !== null) {
            $manager->remove($user);
            $manager->flush();
        }
    }

    /**
     * @param $subscribe
     * @param User $user
     */
    public function updateSubscribe($subscribe, User $user): void
    {
        $manager = $this->doctrine->getManager();
        $user->setIsSubscribe($subscribe);
        $manager->persist($user);
        $manager->flush();
    }
}
