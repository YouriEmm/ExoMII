<?php

namespace App\Controller;

use OpenAI;
use App\Entity\Chapitre;
use App\Entity\Exercice;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\ExerciceType;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/exercice')]
final class ExerciceController extends AbstractController{
    #[Route(name: 'app_exercice_index', methods: ['GET'])]
    public function index(ExerciceRepository $exerciceRepository): Response
    {
        return $this->render('exercice/index.html.twig', [
            'exercices' => $exerciceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_exercice_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $exercice = new Exercice();
        $form = $this->createForm(ExerciceType::class, $exercice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($exercice);
            $entityManager->flush();

            return $this->redirectToRoute('app_exercice_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('exercice/new.html.twig', [
            'exercice' => $exercice,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_exercice_show', methods: ['GET'])]
    public function show(Exercice $exercice): Response
    {
        return $this->render('exercice/show.html.twig', [
            'exercice' => $exercice,
        ]);
    }


    #[Route('/exercice/generate-exercice', name: 'app_exercice_generate', methods: ['POST'])]
    public function generateExerciceByIA(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $matiere = $request->request->get('matiere');
        $chapitreId = $request->request->get('chapitre_id');
        $chapitreName = $request->request->get('chapitre_nom');

        if (!$matiere || !$chapitreId) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $chapitre = $entityManager->getRepository(Chapitre::class)->find($chapitreId);
        
        if (!$chapitre) {
            return new JsonResponse(['error' => 'Chapitre non trouvé'], 404);
        }

        $client = OpenAI::client($_ENV['OPENAI_API_KEY']);

        $prompt = "Tu es un professeur qui prépare un QCM pour ses élèves sur le sujet '$matiere' et le chapitre '$chapitreName'. 
        Chaque question doit être claire, compréhensible et comporter 4 réponses : une correcte et trois incorrectes mais plausibles. 
        Le niveau de difficulté doit être adapté à la matière et au chapitre. 
        Répond uniquement en JSON, sans texte supplémentaire. Exemple : 
        ```json
        {
        \"questions\": [
            {
            \"texte\": \"Quel est le résultat de 2+2 ?\",
            \"reponses\": [
                {\"texte\": \"3\", \"correcte\": false},
                {\"texte\": \"4\", \"correcte\": true},
                {\"texte\": \"5\", \"correcte\": false},
                {\"texte\": \"6\", \"correcte\": false}
            ]
            }
        ]
        }
        ```";

        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'system', 'content' => $prompt]],
        ]);

        $generatedText = $response['choices'][0]['message']['content'];
        $cleanedJson = preg_replace('/```json(.*?)```/s', '$1', $generatedText);
        $data = json_decode(trim($cleanedJson), true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['questions']) || !is_array($data['questions'])) {
            return new JsonResponse(['error' => 'Format de réponse invalide'], 500);
        }

        $exercice = new Exercice();
        $exercice->setTitre("Exercice du chapitre : ". $chapitreName);
        $exercice->setContenu("Exercice généré sur le chapitre ".$chapitreName." de la matière ".$matiere); 
        $exercice->setType("ChoixSimple");

        $exercice->setChapitre($chapitre);

        foreach ($data['questions'] as $q) {
            $question = new Question();
            $question->setTexte($q['texte']);
            $question->setExercice($exercice);

            $entityManager->persist($question);

            foreach ($q['reponses'] as $r) {
                $reponse = new Reponse();
                $reponse->setTexte($r['texte']);
                $reponse->setEstCorrecte($r['correcte']);
                $reponse->setQuestion($question);

                $entityManager->persist($reponse);
            }

            $exercice->addQuestion($question);
        }

        $entityManager->persist($exercice);
        $entityManager->flush();

        return $this->redirectToRoute('admin_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    
}
 