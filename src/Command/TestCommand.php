<?php

namespace App\Command;

use App\MangaPlatform\Platforms\MangaParkPlatform;
use App\Service\ImageService;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Panther\Client;
class TestCommand extends BaseCommand
{
    public static $defaultName = 'pm:test';

    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $em, MailerInterface $mailer)
    {
        parent::__construct($em);

        $this->mailer = $mailer;
    }

    protected function configure()
    {
        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = (new Email())
            ->from('jeremy.dulon@live.fr')
            ->to('j.dulon.64@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($email);

        return 0;
    }
}
