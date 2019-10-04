<?php

namespace App\Command;

use App\Backup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BackupCommand extends Command
{
    protected static $defaultName = 'app:backup';
    
    /**
     * @var Backup
     */
    private $backup;

    public function __construct(Backup $backup)
    {
        parent::__construct();
        $this->backup = $backup;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Do the backup')
            ->addOption('no-overwrite', null, InputOption::VALUE_NONE, 'Do not overwrite if file already exists on target');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->backup->execute($output, $input);
    }
}
