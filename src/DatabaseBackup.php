<?php


namespace App;


use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DatabaseBackup
{

    /**
     * @var FilesystemInterface
     */
    private $targetFs;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(FilesystemInterface $targetFs, EntityManagerInterface $entityManager)
    {
        $this->targetFs = $targetFs;
        $this->entityManager = $entityManager;
    }

    public function execute(OutputInterface $output): void
    {
        $params = $this->entityManager->getConnection()->getParams();
        $host = $params['host'];
        $user = $params['user'];
        $password = $params['password'];
        $name = $params['dbname'];
        $port = $params['port'];
        $filename = time().'_dump.sql';
        $file = sys_get_temp_dir().'/'.$filename;

        $cmd = sprintf(
            'mysqldump -h %s -u %s -p%s -P %s %s > %s',
            $host,
            $user,
            $password,
            $port,
            $name,
            $file
        );
        $process = Process::fromShellCommandline($cmd);
        $output->writeln('[DB Dump] Starting');
        $process->run();
        if ($process->isSuccessful()) {
            $output->writeln('[DB Dump] Copying dump to target');
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->targetFs->write('dbdump/'.$filename, file_get_contents($file));
            $output->writeln('[DB Dump] Done');
        }

        if (! $process->isSuccessful()) {
            $output->write('[DB Dump] Failed: ' . $process->getErrorOutput());
        }
    }

}
