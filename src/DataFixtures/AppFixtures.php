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

        $utilisateurs = [];
        for ($i = 0; $i < 5; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom($faker->lastName)->setEmail($faker->email);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $utilisateur,
                'password'
            );
            $utilisateur->setPassword($hashedPassword);          
            if ($i === 0) {
                $utilisateur->setRoles(['ROLE_ADMIN']);
            } elseif ($i === 4) {
                $utilisateur->setRoles(['ROLE_BANNED']);
            } else {
                $utilisateur->setRoles(['ROLE_USER']);
            }
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
