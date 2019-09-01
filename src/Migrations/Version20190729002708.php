<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190729002708 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE workflow ADD discr VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE object_workflow CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE object_workflow ADD CONSTRAINT FK_B313757EBF396750 FOREIGN KEY (id) REFERENCES workflow (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE object_workflow DROP FOREIGN KEY FK_B313757EBF396750');
        $this->addSql('ALTER TABLE object_workflow CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE workflow DROP discr');
    }
}
