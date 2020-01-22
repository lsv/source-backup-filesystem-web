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
    /**
     * @var string
     */
    private $temporaryDirectory;

    public function __construct(FilesystemInterface $targetFs, EntityManagerInterface $entityManager, string $temporaryDirectory)
    {
        $this->targetFs = $targetFs;
        $this->entityManager = $entityManager;
        $this->temporaryDirectory = $temporaryDirectory;
    }

    public function getProcess(string $path, string $filename): Process
    {
        $params = $this->entityManager->getConnection()->getParams();
        $host = $params['host'];
        $user = $params['user'];
        $password = $params['password'];
        $name = $params['dbname'];
        $port = $params['port'];
        $file = $path.'/'.$filename;

        $cmd = sprintf(
            'mysqldump -q -h %s -u %s -p%s -P %s %s > %s',
            $host,
            $user,
            $password,
            $port,
            $name,
            $file
        );

        return Process::fromShellCommandline($cmd);
    }

    public function execute(OutputInterface $output): void
    {
        $path = $this->temporaryDirectory;
        $filename = time().'_dump.sql';

        $process = $this->getProcess($path, $filename);
        $process->mustRun();
        if ($process->isSuccessful()) {
            $output->writeln('[DB Dump] Copying dump to target');
            $handle = fopen($path . '/' . $filename, 'rb');
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->targetFs->writeStream('dbdump/'.$filename, $handle);
            $output->writeln('[DB Dump] Done');
        }

        if (! $process->isSuccessful()) {
            $output->write('[DB Dump] Failed: ' . $process->getErrorOutput());
        }
    }

}
