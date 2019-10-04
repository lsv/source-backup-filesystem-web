<?php

namespace App\Command;

use App\Compare;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\NamedAddress;

class CompareMailCommand extends Command
{
    protected static $defaultName = 'app:compare:mail';

    /**
     * @var Compare
     */
    private $compare;

    /**
     * @var string
     */
    private $toEmail;

    /**
     * @var string
     */
    private $toName;

    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(MailerInterface $mailer, Compare $compare, string $toEmail, string $toName)
    {
        parent::__construct();
        $this->compare = $compare;
        $this->toEmail = $toEmail;
        $this->toName = $toName;
        $this->mailer = $mailer;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Compare backup systems, and send a mail')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $subject = 'Backup is OK';
        $okFiles = $this->compare->getOkFiles();
        $errorFiles = $this->compare->getErroredFilesByErrorMessage();
        if ($errorFiles) {
            $subject = 'Backup has fails';
        }

        $mail = (new TemplatedEmail())
            ->from(new NamedAddress('monitor@testdsb.com', 'DSB - Monitoring'))
            ->to(new NamedAddress($this->toEmail, $this->toName))
            ->subject($subject)
            ->htmlTemplate('mail/compare.mail.twig')
            ->context([
                'okfiles' => $okFiles,
                'errorfiles' => $errorFiles,
            ]);

        $this->mailer->send($mail);
    }
}
