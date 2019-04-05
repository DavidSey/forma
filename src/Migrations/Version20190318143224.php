<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190318143224 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category_production (category_id INT NOT NULL, production_id INT NOT NULL, INDEX IDX_F5F3E19E12469DE2 (category_id), INDEX IDX_F5F3E19EECC6147F (production_id), PRIMARY KEY(category_id, production_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE picture (id INT AUTO_INCREMENT NOT NULL, id_production_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_16DB4F8952EEC51A (id_production_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE production (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category_production ADD CONSTRAINT FK_F5F3E19E12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_production ADD CONSTRAINT FK_F5F3E19EECC6147F FOREIGN KEY (production_id) REFERENCES production (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE picture ADD CONSTRAINT FK_16DB4F8952EEC51A FOREIGN KEY (id_production_id) REFERENCES production (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category_production DROP FOREIGN KEY FK_F5F3E19E12469DE2');
        $this->addSql('ALTER TABLE category_production DROP FOREIGN KEY FK_F5F3E19EECC6147F');
        $this->addSql('ALTER TABLE picture DROP FOREIGN KEY FK_16DB4F8952EEC51A');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE category_production');
        $this->addSql('DROP TABLE picture');
        $this->addSql('DROP TABLE production');
    }
}
