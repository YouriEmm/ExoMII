<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250207141122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout du delete on cascade pour les cours';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C1FBEEF7B');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES chapitre (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9C1FBEEF7B');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9C1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES chapitre (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
