<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190425213757 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE custom_object (id INT AUTO_INCREMENT NOT NULL, portal_id INT NOT NULL, label VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, system_defined TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9C007FE8B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property (id INT AUTO_INCREMENT NOT NULL, property_group_id INT NOT NULL, custom_object_id INT NOT NULL, label VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, field_type VARCHAR(255) NOT NULL, field JSON DEFAULT NULL, required TINYINT(1) NOT NULL, is_column TINYINT(1) NOT NULL, column_order INT DEFAULT NULL, is_default_property TINYINT(1) NOT NULL, default_property_order INT DEFAULT NULL, system_defined TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8BF21CDEA0D3ED03 (property_group_id), INDEX IDX_8BF21CDE6917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE property_group (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, name VARCHAR(255) NOT NULL, internal_name VARCHAR(255) NOT NULL, system_defined TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_ABD385C86917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE portal (id INT AUTO_INCREMENT NOT NULL, internal_identifier VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, properties JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9B349F916917218D (custom_object_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, portal_id INT NOT NULL, query LONGTEXT NOT NULL, data JSON NOT NULL, name VARCHAR(255) NOT NULL, column_order JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C42F77846917218D (custom_object_id), INDEX IDX_C42F7784B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE marketing_list (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, portal_id INT NOT NULL, query LONGTEXT NOT NULL, data JSON NOT NULL, name VARCHAR(255) NOT NULL, column_order JSON NOT NULL, type VARCHAR(255) NOT NULL, records JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C6EF1A406917218D (custom_object_id), INDEX IDX_C6EF1A40B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, portal_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, is_admin_user TINYINT(1) NOT NULL, first_name VARCHAR(24) NOT NULL, last_name VARCHAR(24) NOT NULL, password_reset_token VARCHAR(64) DEFAULT NULL, password_reset_token_timestamp DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, portal_id INT NOT NULL, name VARCHAR(255) NOT NULL, object_permissions JSON NOT NULL, system_permissions JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_57698A6AB887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter (id INT AUTO_INCREMENT NOT NULL, portal_id INT NOT NULL, custom_filters JSON NOT NULL, query LONGTEXT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_7FC45F1DB887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_object ADD CONSTRAINT FK_9C007FE8B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDEA0D3ED03 FOREIGN KEY (property_group_id) REFERENCES property_group (id)');
        $this->addSql('ALTER TABLE property ADD CONSTRAINT FK_8BF21CDE6917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE property_group ADD CONSTRAINT FK_ABD385C86917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F916917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77846917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE marketing_list ADD CONSTRAINT FK_C6EF1A406917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE marketing_list ADD CONSTRAINT FK_C6EF1A40B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role ADD CONSTRAINT FK_57698A6AB887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1DB887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDE6917218D');
        $this->addSql('ALTER TABLE property_group DROP FOREIGN KEY FK_ABD385C86917218D');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F916917218D');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77846917218D');
        $this->addSql('ALTER TABLE marketing_list DROP FOREIGN KEY FK_C6EF1A406917218D');
        $this->addSql('ALTER TABLE property DROP FOREIGN KEY FK_8BF21CDEA0D3ED03');
        $this->addSql('ALTER TABLE custom_object DROP FOREIGN KEY FK_9C007FE8B887E1DD');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784B887E1DD');
        $this->addSql('ALTER TABLE marketing_list DROP FOREIGN KEY FK_C6EF1A40B887E1DD');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649B887E1DD');
        $this->addSql('ALTER TABLE role DROP FOREIGN KEY FK_57698A6AB887E1DD');
        $this->addSql('ALTER TABLE filter DROP FOREIGN KEY FK_7FC45F1DB887E1DD');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('DROP TABLE custom_object');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_group');
        $this->addSql('DROP TABLE portal');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE marketing_list');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE filter');
    }
}
