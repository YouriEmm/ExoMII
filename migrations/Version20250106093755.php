<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250106093755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'modification de role';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur ADD roles JSON NOT NULL, DROP role');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE utilisateur ADD role VARCHAR(255) NOT NULL, DROP roles');
    }
}
