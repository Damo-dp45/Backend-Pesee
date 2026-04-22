<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260417093841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE operation (id INT AUTO_INCREMENT NOT NULL, mouvement VARCHAR(255) NOT NULL, client VARCHAR(255) NOT NULL, fournisseur VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, produit VARCHAR(255) NOT NULL, transporteur VARCHAR(255) NOT NULL, provenance VARCHAR(255) NOT NULL, chauffeur VARCHAR(255) NOT NULL, immatriculation VARCHAR(255) NOT NULL, remorque VARCHAR(255) DEFAULT NULL, poids1 INT NOT NULL, poids2 INT NOT NULL, poidsbrut INT NOT NULL, poidsnet INT NOT NULL, date1 DATE NOT NULL, date2 DATE NOT NULL, temps1 TIME NOT NULL, temps2 TIME NOT NULL, datesearch DATE NOT NULL, codepesee VARCHAR(255) NOT NULL, numticket VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, codesite VARCHAR(255) NOT NULL, codesecret VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE operation');
    }
}
