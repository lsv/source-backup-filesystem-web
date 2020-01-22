<?php

namespace App\Command;

use App\Backup;
use App\DatabaseBackup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

    /**
     * @var DatabaseBackup
     */
    private $databaseBackup;

    public function __construct(Backup $backup, DatabaseBackup $databaseBackup)
    {
        parent::__construct();
        $this->backup = $backup;
        $this->databaseBackup = $databaseBackup;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Do the backup')
            ->addOption('no-overwrite', null, InputOption::VALUE_NONE, 'Do not overwrite if file already exists on target')
            ->addArgument('type', InputArgument::OPTIONAL, 'Which backup type should be used (file, database)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        switch ($input->getArgument('type')) {
            case 'file':
                $this->backup->execute($output, $input);
                break;
            case 'database':
                $this->databaseBackup->execute($output);
                break;
            case null:
                $this->databaseBackup->execute($output);
                $this->backup->execute($output, $input);
                break;
            default:
                $output->writeln('Backup type does not exists');
        }

    }
}
