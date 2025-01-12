<?php

// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\ResultatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile_show')]
    public function show(ResultatRepository $resultatRepository): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        
        $resultats = $resultatRepository->findBy(['user' => $user]);

        return $this->render('profile.html.twig', [
            'user' => $user,
            'resultats' => $resultats,
        ]);
    }
    
}


?>