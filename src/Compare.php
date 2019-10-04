<?php

namespace App;

use Lsv\BackupCompareFilesystems\CompareFilesystems;
use Lsv\BackupCompareFilesystems\Model\File;

class Compare
{

    /**
     * @var CompareFilesystems
     */
    private $compare;

    static private $errorMessages = [
        File::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP => 'Source file does not exists in backup',
        File::SOURCE_FILE_IS_SMALLER_THAN_BACKUP => 'Source file is smaller than backup',
        File::SOURCE_FILE_IS_LARGER_THAN_BACKUP => 'Source file is larger than backup',
        File::SOURCE_FILE_IS_OLDER_THAN_BACUP => 'Source file is older than backup',
        File::SOURCE_FILE_IS_NEWER_THAN_BACKUP => 'Source file is newer than backup',
    ];

    public function __construct(CompareFilesystems $compare)
    {
        $this->compare = $compare;
    }

    public function getFiles(): array
    {
        static $files = [];
        if (! $files) {
            foreach ($this->compare->compare() as $file) {
                $files[] = [
                    'path' => $file->getPath(),
                    'filename' => $file->getFilename(),
                    'sourceTimestamp' => $file->getSourceTimestamp(),
                    'sourceSize' => $file->getSourceSize(),
                    'targetTimestamp' => $file->getTargetTimestamp(),
                    'targetSize' => $file->getTargetSize(),
                    'errors' => $this->parseErrors($file->getErrors()),
                ];
            }
        }

        return $files;
    }

    public function getOkFiles(): array
    {
        return array_filter($this->getFiles(), static function ($file) {
            return count($file['errors']) === 0;
        });
    }

    public function getErroredFilesByErrorMessage(): array
    {
        $errorfiles = [];
        foreach ($this->getFiles() as $file) {
            foreach ($file['errors'] as $error) {
                if (in_array($error, self::$errorMessages, true)) {
                    $errorfiles[$error][] = $file;
                } else {
                    $errorfiles['Unknown error'][] = $file;
                }
            }
        }
        return $errorfiles;
    }

    private function parseErrors(array $errors): array
    {
        $out = [];
        foreach ($errors as $error) {
            if (array_key_exists($error, self::$errorMessages)) {
                $out[] = self::$errorMessages[$error];
            }
        }

        return $out;
    }

}
