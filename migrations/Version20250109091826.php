<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250109091826 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout du user dans les resultats';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resultat ADD user_id INT DEFAULT NULL, ADD total_questions INT NOT NULL');
        $this->addSql('ALTER TABLE resultat ADD CONSTRAINT FK_E7DB5DE2A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_E7DB5DE2A76ED395 ON resultat (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE resultat DROP FOREIGN KEY FK_E7DB5DE2A76ED395');
        $this->addSql('DROP INDEX IDX_E7DB5DE2A76ED395 ON resultat');
        $this->addSql('ALTER TABLE resultat DROP user_id, DROP total_questions');
    }
}
