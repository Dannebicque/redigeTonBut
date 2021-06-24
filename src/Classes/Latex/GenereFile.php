<?php


namespace App\Classes\Latex;


use App\Entity\Departement;
use DateTime;
use Twig\Environment;

class GenereFile
{
    protected Departement $departement;
    protected Environment $twig;
    protected string $chemin;


    public function __construct(Environment $twig, string $chemin, Departement $departement)
    {
        $this->chemin = $chemin;
        $this->twig = $twig;
        $this->departement = $departement;
    }

    public function genereFile()
    {

        $content = $this->twig->render('latex/modele.tex.twig', [
            'departement' => $this->departement
        ]);
        $cle = new DateTime('now');
        $name = $this->chemin . 'PN-BUT-' . $this->departement->getSigle() . '-' . $cle->format('dmY-Hi') . '.tex';
        $fichier = fopen($name, 'wb+');
        fwrite($fichier, $content);
        fclose($fichier);

        return $name;

    }
}
