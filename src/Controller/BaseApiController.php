<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class BaseApiController extends AbstractController
{
    protected function checkAccessApi(Request $request)
    {
        if (!$request->headers->has('x-api-key')) {
            throw $this->createAccessDeniedException('Accès non autorisé');
        }

        if ($request->headers->get('x-api-key') !== $this->getParameter('api_key')) {
            throw $this->createAccessDeniedException('Accès non autorisé');
        }
    }
}
