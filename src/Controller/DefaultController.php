<?php
namespace App\Controller;

use App\Entity\Matiere;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'home')]
    public function index()
    {
        $matieres = $this->entityManager->getRepository(Matiere::class)->findAll();

        return $this->render('home.html.twig', [
            'matieres' => $matieres,
        ]);
    }
}
?>