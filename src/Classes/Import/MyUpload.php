<?php
/*
 * Copyright (c) 2021. | David Annebicque | IUT de Troyes  - All Rights Reserved
 * @file /Users/davidannebicque/htdocs/intranetV3/src/Classes/MyUpload.php
 * @author davidannebicque
 * @project intranetV3
 * @lastUpdate 13/05/2021 12:31
 */

namespace App\Classes\Import;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MyUpload
{
    private $dir;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->dir = $parameterBag->get('kernel.project_dir') . '/public/upload/';
    }

    /**
     * @throws Exception
     */
    public function upload(UploadedFile $fichier, string $destination, array $extensions = []): ?string
    {
        $extension = $this->getExtension($fichier);
        $dir = $this->valideDir($destination);

        if (null !== $fichier) {
            if ((\count($extensions) > 0) && !\in_array($extension, $extensions, true)) {
                throw new Exception();
            }

            $nomfile = random_int(1, 99999) . '_' . date('YmdHis') . '.' . $extension;
            $fichier->move($this->dir . $dir, $nomfile);

            return $this->dir . $dir . $nomfile;
        }

        return null;
    }

    private function valideDir($dir)
    {
        if ('/' === $dir[0]) {
            $dir = mb_substr($dir, 1, mb_strlen($dir));
        }

        if ('/' !== $dir[mb_strlen($dir) - 1]) {
            $dir .= '/';
        }

        return $dir;
    }

    public function getExtension(UploadedFile $fichier): string
    {
        return $fichier->getClientOriginalExtension();
    }
}
