<?php

namespace App\Controller;

use App\Entity\Matiere;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(EntityManagerInterface $entityManager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        $matieres = $entityManager->getRepository(Matiere::class)->findAll();

        return $this->render('admin_dashboard.html.twig', [
            'matieres' => $matieres,
        ]);
    }
}


?>