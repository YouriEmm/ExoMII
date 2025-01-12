<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Utilisateur;
use App\Entity\Matiere;
use App\Entity\Chapitre;
use App\Entity\Exercice;
use App\Entity\Question;
use App\Entity\Reponse;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $utilisateursData = [
            ['nom' => 'User1', 'email' => 'user1@exemple.com', 'role' => 'ROLE_ADMIN'],
            ['nom' => 'User2', 'email' => 'user2@exemple.com', 'role' => 'ROLE_USER'],
            ['nom' => 'User3', 'email' => 'user3@exemple.com', 'role' => 'ROLE_BANNED'],
        ];
        
        $utilisateurs = [];
        foreach ($utilisateursData as $data) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($data['nom'])
                ->setEmail($data['email'])
                ->setRoles([$data['role']]);
        
            $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, 'donne20sur20stp');
            $utilisateur->setPassword($hashedPassword);
        
            $manager->persist($utilisateur);
            $utilisateurs[] = $utilisateur;
        }
        

        $matieres2 = [
            'Mathématiques', 
            'Physique', 
            'Chimie', 
            'Informatique', 
            'Biologie'
        ];

        foreach ($matieres2 as $matiereName) {
            $matiere = new Matiere();
            $matiere->setNom($matiereName);
            $manager->persist($matiere);
            $matieres[] = $matiere;
        }

        $chapitres = [];
        foreach ($matieres as $matiere) {
            for ($i = 0; $i < 5; $i++) {
                $chapitre = new Chapitre();
                $chapitre->setNom('Chapitre ' . ($i + 1) . ' de ' . $matiere->getNom())
                    ->setDescription($faker->sentence)
                    ->setMatiere($matiere);
                $manager->persist($chapitre);
                $chapitres[] = $chapitre;
            }
        }
        

        $exercices = [];
        foreach ($chapitres as $chapitre) {
            for ($i = 0; $i < 5; $i++) {
                $exercice = new Exercice();
                $exercice->setTitre('Exercice ' . ($i + 1))
                    ->setContenu($faker->paragraph)
                    ->setType($i % 2 === 0 ? 'ChoixSimple' : 'ChoixMultiple')
                    ->setNiveau($faker->randomElement(['Facile', 'Moyen', 'Difficile']))
                    ->setChapitre($chapitre);
                $manager->persist($exercice);
                $exercices[] = $exercice;
            }
        }

        $questions = [];
        foreach ($exercices as $exercice) {
            for ($i = 0; $i < 5; $i++) {
                $question = new Question();
                $question->setTexte('Question ' . ($i + 1) . ' de ' . $exercice->getTitre())
                    ->setExercice($exercice);
                $manager->persist($question);
                $questions[] = $question;
            }
        }

        foreach ($questions as $question) {
            for ($i = 0; $i < 4; $i++) {
                $reponse = new Reponse();
                $reponse->setTexte($faker->sentence)
                    ->setEstCorrecte($i === 0)  
                    ->setQuestion($question);
                $manager->persist($reponse);
            }
        }

        $manager->flush();
    }
}
