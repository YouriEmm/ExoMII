<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250210104731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Inversion de chapitre et cours pour que chapitre soit au courant de l existence des cours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours CHANGE chapitre_id chapitre_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours CHANGE chapitre_id chapitre_id INT DEFAULT NULL');
    }
}
