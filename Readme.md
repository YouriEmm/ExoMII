# Projet Symfony - Instructions de Configuration

Merci à Richard pour sa participation incroyable dans la création du style absolument magnifique(non) du site

## Prérequis

- **PHP 8.1 ou supérieur**
- **Composer**
- **Node.js** 
- **Symfony CLI**
- **Serveur SQL**

---

## Étapes d'installation

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/YouriEmm/ExoMII
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Configurer les variables d'environnement**
   - Modifie le fichier `.env` pour mettre les infos de connexion à la BDD à la ligne du  "DATABASE_URL=..."

4. **Créer la BDD**
   ```bash
   php bin/console doctrine:database:create
   ```

5. **Migration**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Fixtures**
   ```bash
   php bin/console doctrine:fixtures:load
   ```

7. **Lancer le serveur local**
     ```bash
     symfony server:start
     ```
---

## Utilisateurs

user1@exemple.com - ROLE_ADMIN - donne20sur20stp
user2@exemple.com - ROLE_USER - donne20sur20stp
user3@exemple.com - ROLE_BANNED - donne20sur20stp

