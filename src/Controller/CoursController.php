<?php

namespace App\Controller;

use OpenAI;
use App\Entity\Cours;
use App\Entity\Chapitre;
use App\Form\CoursType;
use App\Repository\CoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/cours')]
final class CoursController extends AbstractController{
    #[Route(name: 'app_cours_index', methods: ['GET'])]
    public function index(CoursRepository $coursRepository): Response
    {
        return $this->render('cours/index.html.twig', [
            'cours' => $coursRepository->findAll(),
        ]);
    }

    #[Route('/new/{chapitreId}', name: 'app_cours_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $chapitreId): Response
    {
        $chapitre = $entityManager->getRepository(Chapitre::class)->find($chapitreId);
    
        if (!$chapitre) {
            throw $this->createNotFoundException('Chapitre introuvable');
        }
    
        $cour = new Cours();
        $cour->setChapitre($chapitre);
    
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cour);
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('cours/new.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }   
    

    #[Route('/{id}', name: 'app_cours_show', methods: ['GET'])]
    public function show(int $id, CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->findOneBy(['chapitre' => $id]);
    
        return $this->render('cours/show.html.twig', [
            'cour' => $cours,
        ]);
    }
    

    #[Route('/{id}/edit', name: 'app_cours_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CoursType::class, $cour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('cours/edit.html.twig', [
            'cour' => $cour,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cours_delete', methods: ['POST'])]
    public function delete(Request $request, Cours $cour, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cour->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($cour);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cours_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('cours/generate-cours', name: 'app_cours_generate', methods: ['POST'])]
    public function generateCoursByIA(Request $request, EntityManagerInterface $entityManager): Response
    {
        $matiere = trim($request->request->get('matiere', ''));
        $chapitreId = trim($request->request->get('chapitre_id', ''));
        $chapitreName = trim($request->request->get('chapitre_nom', ''));
    
        if (empty($matiere) || empty($chapitreId) || empty($chapitreName)) {
            return new JsonResponse(['error' => 'Données manquantes ou invalides'], 400);
        }
    
        $chapitre = $entityManager->getRepository(Chapitre::class)->find($chapitreId);
    
        if (!$chapitre) {
            return new JsonResponse(['error' => 'Chapitre non trouvé'], 404);
        }
    
        try {
            $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
    
            $prompt = "Tu es un professeur qui rédige un cours complet contenant différentes partie d'au minimum 100 lignes sur la matière : '$matiere', et le chapitre '$chapitreName'. Voici le format attendu pour le cours en JSON : \n" .
                      "{\n\"titre\": \"Le titre du cours ici\",\n\"contenu\": \"Le contenu du cours ici\",\n\"niveau\": \"niveau de difficulté du cours ici\"\n}\n" .
                      "Réponds uniquement avec ce format JSON, sans autre texte.";
    
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [['role' => 'system', 'content' => $prompt]],
            ]);
    
            if (!isset($response['choices'][0]['message']['content'])) {
                return new JsonResponse(['error' => 'Réponse OpenAI invalide'], 500);
            }
    
            $generatedText = $response['choices'][0]['message']['content'];
    
            if (!is_string($generatedText) || !json_decode($generatedText)) {
                return new JsonResponse(['error' => 'Réponse OpenAI non formatée correctement'], 500);
            }
    
            $data = json_decode($generatedText, true);
    
            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['titre']) || !isset($data['contenu']) || !isset($data['niveau'])) {
                return new JsonResponse(['error' => 'Format de réponse invalide: ' . json_last_error_msg()], 500);
            }
    
            $formatTexte = function ($contenu): string {
                if (is_array($contenu)) {
                    return implode("\n\n", array_map(function($item) {
                        return is_array($item) ? implode("\n", $item) : (string) $item;
                    }, $contenu));
                }
                return (string) trim($contenu);
            };
    
            $texteCours = $formatTexte($data['contenu']);
    
            $niveau = $data['niveau'];
    
            $cours = new Cours();
            $cours->setNom($data['titre']);
            $cours->setShortDescription($data['titre']);
            $cours->setTexte($texteCours);
            $cours->setChapitre($chapitre);
            $cours->setNiveau($niveau);
            $cours->setDuree(rand(20, 100));
    
            $entityManager->persist($cours);
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la génération du cours: ' . $e->getMessage()], 500);
        }
    }
    

    
}
