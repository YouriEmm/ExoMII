<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250206091611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout d un cours par chapitre de matière';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, chapitre_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, short_description VARCHAR(255) NOT NULL, texte LONGTEXT DEFAULT NULL, niveau VARCHAR(255) NOT NULL, duree INT NOT NULL, UNIQUE INDEX UNIQ_FDCA8C9C1FBEEF7B (chapitre_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES chapitre (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C1FBEEF7B');
        $this->addSql('DROP TABLE cours');
    }
}
