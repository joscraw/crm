<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107073630 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE custom_object (id INT AUTO_INCREMENT NOT NULL, portal_id INT NOT NULL, label VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9C007FE8B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE portal (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property (id INT AUTO_INCREMENT NOT NULL, property_group_id INT NOT NULL, custom_object_id INT NOT NULL, label VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, field_type VARCHAR(255) NOT NULL, field JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8BF21CDEA0D3ED03 (property_group_id), INDEX IDX_8BF21CDE6917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_group (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_ABD385C86917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_object ADD CONSTRAINT FK_9C007FE8B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEA0D3ED03 FOREIGN KEY (property_group_id) REFERENCES property_group (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE6917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE property_group ADD CONSTRAINT FK_ABD385C86917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE6917218D');
        $this->addSql('ALTER TABLE property_group DROP FOREIGN KEY FK_ABD385C86917218D');
        $this->addSql('ALTER TABLE custom_object DROP FOREIGN KEY FK_9C007FE8B887E1DD');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDEA0D3ED03');
        $this->addSql('DROP TABLE custom_object');
        $this->addSql('DROP TABLE portal');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_group');
    }
}
