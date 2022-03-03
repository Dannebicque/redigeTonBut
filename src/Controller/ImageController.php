<?php

namespace App\Controller;

use App\Repository\ApcRessourceRepository;
use App\Repository\ApcSaeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends AbstractController
{
    #[Route('/image/{id}/{type}', name: 'image')]
    public function index(KernelInterface $kernel,
        ApcSaeRepository $apcSaeRepository,
        ApcRessourceRepository $apcRessourceRepository,
        $id, $type): Response
    {
        $im = imagecreate(50, 200);
        if ($type === 'sae') {
            $sae = $apcSaeRepository->find($id);
            $fond = imagecolorallocate($im, 173, 216, 230);
            $texte = $sae->getCodeMatiere() . ' '. $sae->getLibelle();
        } else {
            $fond = imagecolorallocate($im, 255, 255, 255);
            $ressource = $apcRessourceRepository->find($id);
            $texte = $ressource->getCodeMatiere() . ' '. $ressource->getLibelle();
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
       // $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s.png"', $text));



        $noir = imagecolorallocate($im, 0, 0, 0);
        imagefill($im, 0, 0, $fond);
        $font = $kernel->getProjectDir() . '/public/arial.ttf';
        imagettftext($im, 10, 90, 15, 190, $noir, $font, $texte);
        imagepng($im, $kernel->getProjectDir() . '/public/tableaux/11/' . $type.'_'.$id . '.png');
        imagedestroy($im);

        return $response;
    }
}
