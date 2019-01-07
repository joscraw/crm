<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107012314 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE property_group ADD custom_object_id INT NOT NULL');
        $this->addSql('ALTER TABLE property_group ADD CONSTRAINT FK_ABD385C86917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('CREATE INDEX IDX_ABD385C86917218D ON property_group (custom_object_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE property_group DROP FOREIGN KEY FK_ABD385C86917218D');
        $this->addSql('DROP INDEX IDX_ABD385C86917218D ON property_group');
        $this->addSql('ALTER TABLE property_group DROP custom_object_id');
    }
}
