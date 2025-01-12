<?php

namespace App\Controller;

use App\Entity\Matiere;
use App\Entity\Chapitre;
use App\Entity\Exercice;
use App\Entity\Resultat;
use App\Form\MatiereType;
use App\Repository\MatiereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/matiere')]
final class MatiereController extends AbstractController{


    #[Route('/matiere/{id}', name: 'matiere_show')]
    public function show(Matiere $matiere)
    {
        return $this->render('show.html.twig', [
            'matiere' => $matiere,
            'chapitres' => $matiere->getChapitres(),
        ]);
    }
    
    #[Route('/matiere/{id}/exercices', name: 'chapitre_exercices')]
    public function showExercices(Chapitre $chapitre)
    {
        return $this->render('exercices.html.twig', [
            'chapitre' => $chapitre,
            'exercices' => $chapitre->getExercices(),
        ]);
    }

    #[Route('/exercice/{id}/questions', name: 'exercice_questions')]
    public function showQuestions(Exercice $exercice): Response
    {
        $totalQuestions = count($exercice->getQuestions());

        return $this->render('questions.html.twig', [
            'exercice' => $exercice,
            'questions' => $exercice->getQuestions(),
            'totalQuestions' => $totalQuestions,
        ]);
    }
    
    #[Route('/exercice/{id}/submit', name: 'exercice_submit', methods: ['POST'])]
    public function submitExercice(Exercice $exercice, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour soumettre vos réponses.');
            return $this->redirectToRoute('app_login');
        }
    
        $questions = $exercice->getQuestions();
        $submittedAnswers = $request->request->all();
    
        $score = 0;
        $totalQuestions = count($questions);
    
        foreach ($questions as $question) {
            $correctAnswer = $question->getReponses()->filter(fn($reponse) => $reponse->isEstCorrecte())->first();
        
            if ($correctAnswer && isset($submittedAnswers['question_' . $question->getId()]) &&
                $submittedAnswers['question_' . $question->getId()] == $correctAnswer->getId()) {
                $score++;
            }
        }
        
        $resultat = new Resultat();
        $resultat->setUser($user)
            ->setExercice($exercice)
            ->setScore($score)
            ->setTotalQuestions($totalQuestions);
    
        $em->persist($resultat);
        $em->flush();
    
        $chapitre = $exercice->getChapitre();
    
        return $this->render('questions.html.twig', [
            'exercice' => $exercice,
            'questions' => $questions,
            'score' => $score,
            'totalQuestions' => $totalQuestions,
            'chapitre' => $chapitre, 
        ]);
    }

    #[Route(name: 'app_matiere_index', methods: ['GET'])]
    public function index(MatiereRepository $matiereRepository): Response
    {
        return $this->render('matiere/index.html.twig', [
            'matieres' => $matiereRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_matiere_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $matiere = new Matiere();
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($matiere);
            $entityManager->flush();

            return $this->redirectToRoute('app_matiere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matiere/new.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_matiere_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Matiere $matiere, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_matiere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('matiere/edit.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_matiere_delete', methods: ['POST'])]
    public function delete(Request $request, Matiere $matiere, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}
