<?php

namespace App\Pdf;

enum PdfSourceType: string
{
    case ARGUMENTAIRE = 'argumentaire';
    case RESSOURCE = 'ressource';
    case SAE = 'sae';
    case REFERENTIEL = 'referentiel';
}
