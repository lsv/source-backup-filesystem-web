<?php

namespace App\Controller;

use App\Compare;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @param Compare $compare
     *
     * @return Response
     */
    public function files(Compare $compare): Response
    {
        return $this->json($compare->getFiles());
    }

}
