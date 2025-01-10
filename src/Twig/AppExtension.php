<?php

namespace App\Twig;

use App\Repository\MatiereRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private MatiereRepository $matiereRepository;

    public function __construct(MatiereRepository $matiereRepository)
    {
        $this->matiereRepository = $matiereRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_matieres', [$this, 'getMatieres']),
        ];
    }

    public function getMatieres(): array
    {
        return $this->matiereRepository->findAll();
    }
}
