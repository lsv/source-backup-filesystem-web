<?php

namespace App;

use Aws\Exception\AwsException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Backup
{

    /**
     * @var FilesystemInterface
     */
    private $source;

    /**
     * @var FilesystemInterface
     */
    private $target;

    /**
     * @var InputInterface
     */
    private $input;

    public function __construct(FilesystemInterface $source, FilesystemInterface $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function execute(OutputInterface $output, InputInterface $input): void
    {
        $this->input = $input;
        $this->readDir($output, '.');
    }

    private function readDir(OutputInterface $output, string $path): void
    {
        $output->writeln('Reading dir: "'.$path.'"');
        $files = $this->source->listContents($path);
        foreach ($files as $file) {
            switch ($file['type']) {
                case 'dir':
                    $this->readDir($output, $file['path']);
                    break;
                case 'file':
                    $this->copyFile($output, $file);
                    break;
            }
        }
    }
    private function copyFile(OutputInterface $output, array $filedata): void
    {
        $output->writeln('Handling file: "'.$filedata['path'].'"');
        try {
            $method = 'putStream';
            if (!$this->input->getOption('no-overwrite')) {
                $method = 'writeStream';
            }

            $write = $this->target->$method(
                $filedata['path'],
                $this->source->readStream($filedata['path']),
                [
                    'visibility' => 'private'
                ]
            );
            if ($write) {
                $output->writeln('<info>'.$filedata['path'].' copied successfully</info>');
            } else {
                $output->writeln('<error>'.$filedata['path'].' not copied successfully</error>');
            }
        } catch (AwsException $exception) {
            $output->writeln($exception->getMessage());
        } catch (FileExistsException $e) {
            $output->writeln('<error>'.$filedata['path'].' already exists on target</error>');
        } catch (FileNotFoundException $e) {
            $output->writeln('<error>'.$filedata['path'].' does not exists on source</error>');
        }
    }

}
