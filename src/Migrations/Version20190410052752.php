<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190410052752 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE marketing_list ADD custom_object_id INT NOT NULL, ADD portal_id INT NOT NULL, ADD data JSON NOT NULL, ADD name VARCHAR(255) NOT NULL, ADD column_order JSON NOT NULL');
        $this->addSql('ALTER TABLE marketing_list ADD CONSTRAINT FK_C6EF1A406917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE marketing_list ADD CONSTRAINT FK_C6EF1A40B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('CREATE INDEX IDX_C6EF1A406917218D ON marketing_list (custom_object_id)');
        $this->addSql('CREATE INDEX IDX_C6EF1A40B887E1DD ON marketing_list (portal_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE marketing_list DROP FOREIGN KEY FK_C6EF1A406917218D');
        $this->addSql('ALTER TABLE marketing_list DROP FOREIGN KEY FK_C6EF1A40B887E1DD');
        $this->addSql('DROP INDEX IDX_C6EF1A406917218D ON marketing_list');
        $this->addSql('DROP INDEX IDX_C6EF1A40B887E1DD ON marketing_list');
        $this->addSql('ALTER TABLE marketing_list DROP custom_object_id, DROP portal_id, DROP data, DROP name, DROP column_order');
    }
}
