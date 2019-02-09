<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * @codeCoverageIgnore
 */
final class Version20190208012726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'CREATE TABLE product ('
            .'id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, '
            .'available INT NOT NULL, vat_rate DOUBLE PRECISION NOT NULL, '
            .'created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, '
            .'price_euros INT NOT NULL, price_cents INT NOT NULL, '
            .'UNIQUE INDEX UNIQ_D34A04AD5E237E06 (name), PRIMARY KEY(id)) '
            .'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE cart_item ('
            .'id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, '
            .'product_id INT NOT NULL, quantity INT NOT NULL, '
            .'created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, '
            .'INDEX IDX_F0FE2527A76ED395 (user_id), '
            .'INDEX IDX_F0FE25274584665A (product_id), PRIMARY KEY(id)) '
            .'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE `user` ('
            .'id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, '
            .'roles JSON NOT NULL, password VARCHAR(255) NOT NULL, '
            .'created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, '
            .'UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) '
            .'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527A76ED395 '
            .'FOREIGN KEY (user_id) REFERENCES user (id)'
        );
        $this->addSql(
            'ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A '
            .'FOREIGN KEY (product_id) REFERENCES product (id)'
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            'mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE2527A76ED395');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE `user`');
    }
}
