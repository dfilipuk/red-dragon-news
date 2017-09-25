<?php
/**
 * Created by PhpStorm.
 * User: Алексей
 * Date: 22.09.2017
 * Time: 21:55
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DailyEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:subscription:daily')
            ->setDescription('Send daily email');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $manager = $this->getContainer()->get('subscription_manager');
        $manager->sendSubscriptionEmails('daily');
    }
}
