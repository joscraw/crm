<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190427181844 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE marketing_list ADD folder_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE marketing_list ADD CONSTRAINT FK_C6EF1A40162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id)');
        $this->addSql('CREATE INDEX IDX_C6EF1A40162CB942 ON marketing_list (folder_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE marketing_list DROP FOREIGN KEY FK_C6EF1A40162CB942');
        $this->addSql('DROP INDEX IDX_C6EF1A40162CB942 ON marketing_list');
        $this->addSql('ALTER TABLE marketing_list DROP folder_id');
    }
}
