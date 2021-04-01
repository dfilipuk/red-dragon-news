<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 22.09.2017
 * Time: 22:35
 */

namespace AppBundle\Service;

use AppBundle\Entity\Article;
use AppBundle\Entity\Subscription;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class SubscriptionManager
{
    private const USERS_PER_ITERATION = 2;
    private $doctrine;
    private $mailManager;

    /**
     * SubscriptionManager constructor.
     * @param ManagerRegistry $doctrine
     * @param MailManager $mailManager
     */
    public function __construct(ManagerRegistry $doctrine, MailManager $mailManager)
    {
        $this->doctrine = $doctrine;
        $this->mailManager = $mailManager;
    }

    /**
     * @param string $type
     * @return array|null
     */
    private function getPosts(string $type): ?array
    {
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(Article::class);

        $time = new \DateTime();

        switch ($type) {
            case 'daily':
                $timeFrom = $time->modify('-1 day');
                break;
            case 'weekly':
                $timeFrom = $time->modify('-1 week');
                break;

            case 'monthly':
                $timeFrom = $time->modify('-1 month');
                break;
            default:
                $timeFrom = $time->modify('-1 week');
        }

        return $repository->getArticlesAfterTime($timeFrom);
    }


    /**
     * @param string $type
     */
    public function sendSubscriptionEmails(string $type): void
    {
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(Subscription::class);
        $usersCount = $repository->getSubscribedUsersCount($type);
        $posts = $this->getPosts($type);

        $sendIterationCount = ceil($usersCount / self::USERS_PER_ITERATION);
        if ($posts !== null) {
            $mailBody = $this->mailManager->generateSubscriptionMailBody($posts, $type);
            for ($i = 0; $i < $sendIterationCount; $i++) {
                $offset = $i * self::USERS_PER_ITERATION;
                $notifyedUsers = $repository->getSubscribedUsers($type, $offset, self::USERS_PER_ITERATION);
                foreach ($notifyedUsers as $notifyedUser) {
                    $this->mailManager->sendSubscriptionEmail($notifyedUser->getUserID(), $mailBody);
                    $manager->detach($notifyedUser);
                }
                $manager->clear();
            }
        }
    }

    /**
     * @param User $user
     * @param null|string $type
     */
    public function subscribeUser(User $user, ?string $type): void
    {
        $manager = $this->doctrine->getManager();
        $repository = $manager->getRepository(Subscription::class);
        if ($type === null) {
            $subscription = $repository->findOneBy(['userID' => $user->getId()]);
            $manager->remove($subscription);
            $manager->flush();
        } else {
            $subscription = new Subscription();
            $subscription->setUserID($user);
            $subscription->setType($type);
            $manager->persist($subscription);
            $manager->flush();
        }
    }
}
