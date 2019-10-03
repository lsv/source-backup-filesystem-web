<?php

namespace App\Controller;

use Lsv\BackupCompareFilesystems\CompareFilesystems;
use Lsv\BackupCompareFilesystems\Model\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class IndexController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index(): Response
    {
        return $this->render('index/index.html.twig');
    }

    /**
     * @Route("/files")
     *
     * @param CompareFilesystems $compareFilesystems
     *
     * @return Response
     */
    public function files(CompareFilesystems $compareFilesystems): Response
    {
        $files = [];
        foreach ($compareFilesystems->compare() as $file) {
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

        return $this->json($files);
    }

    private function parseErrors(array $errors): array
    {
        $out = [];
        foreach ($errors as $error) {
            switch ($error) {
                case File::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP:
                    $out[] = 'Source file does not exists in backup';
                    break;
                case File::SOURCE_FILE_IS_SMALLER_THAN_BACKUP:
                    $out[] = 'Source file is smaller than backup';
                    break;
                case File::SOURCE_FILE_IS_LARGER_THAN_BACKUP:
                    $out[] = 'Source file is larger than backup';
                    break;
                case File::SOURCE_FILE_IS_OLDER_THAN_BACUP:
                    $out[] = 'Source file is older than backup';
                    break;
                case File::SOURCE_FILE_IS_NEWER_THAN_BACKUP:
                    $out[] = 'Source file is newer than backup';
                    break;
            }
        }

        return $out;
    }
}
