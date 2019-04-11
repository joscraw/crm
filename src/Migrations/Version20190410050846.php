<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190410050846 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE crmlist (id INT AUTO_INCREMENT NOT NULL, custom_object_id INT NOT NULL, portal_id INT NOT NULL, query LONGTEXT NOT NULL, data JSON NOT NULL, name VARCHAR(255) NOT NULL, column_order JSON NOT NULL, INDEX IDX_65F6B1346917218D (custom_object_id), INDEX IDX_65F6B134B887E1DD (portal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE crmlist ADD CONSTRAINT FK_65F6B1346917218D FOREIGN KEY (custom_object_id) REFERENCES custom_object (id)');
        $this->addSql('ALTER TABLE crmlist ADD CONSTRAINT FK_65F6B134B887E1DD FOREIGN KEY (portal_id) REFERENCES portal (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE crmlist');
    }
}
