<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181231173711 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE property (id INT AUTO_INCREMENT NOT NULL, property_group_id INT NOT NULL, custom_object_id INT NOT NULL, label VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, field_type VARCHAR(255) NOT NULL, field JSON NOT NULL, UNIQUE INDEX UNIQ_8BF21CDEA0D3ED03 (property_group_id), UNIQUE INDEX UNIQ_8BF21CDE6917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEA0D3ED03 FOREIGN KEY (property_group_id) REFERENCES property_group (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE6917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE custom_object DROP content');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE property');
        $this->addSql('ALTER TABLE custom_object ADD content LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci');
    }
}
