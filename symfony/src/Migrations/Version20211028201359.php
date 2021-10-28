<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211028201359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car (id INT AUTO_INCREMENT NOT NULL, brand_id INT NOT NULL, showroom_id INT NOT NULL, model VARCHAR(256) DEFAULT \'\' NOT NULL, INDEX IDX_773DE69D44F5D008 (brand_id), INDEX IDX_773DE69D2243B88B (showroom_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE car_brand (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(256) DEFAULT \'\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, car_id INT NOT NULL, user_manager_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6117D13BA76ED395 (user_id), UNIQUE INDEX UNIQ_6117D13BC3C6F69F (car_id), INDEX IDX_6117D13BDF59F28F (user_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE showroom (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(256) DEFAULT \'\' NOT NULL, address VARCHAR(256) DEFAULT \'\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, showroom_id INT DEFAULT NULL, first_name VARCHAR(100) DEFAULT \'\' NOT NULL, last_name VARCHAR(100) DEFAULT \'\' NOT NULL, patronymic VARCHAR(100) DEFAULT \'\' NOT NULL, phone VARCHAR(20) DEFAULT \'\' NOT NULL, password VARCHAR(100) DEFAULT \'\' NOT NULL, manager TINYINT(1) DEFAULT \'0\' NOT NULL, director TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), INDEX IDX_8D93D6492243B88B (showroom_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D44F5D008 FOREIGN KEY (brand_id) REFERENCES car_brand (id)');
        $this->addSql('ALTER TABLE car ADD CONSTRAINT FK_773DE69D2243B88B FOREIGN KEY (showroom_id) REFERENCES showroom (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BDF59F28F FOREIGN KEY (user_manager_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6492243B88B FOREIGN KEY (showroom_id) REFERENCES showroom (id)');
        $this->addSql('INSERT INTO user (first_name, phone, password, director) VALUES (\'Guest\', \'\', \'\', 0), (\'Director\', \'12345\', \'$2y$13$EQ4Jqi6i/oSk99rlTTlx3uB1uuCN8nZA7lnqVR9/Sq60EzMV3JpxK\', 1)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BC3C6F69F');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D44F5D008');
        $this->addSql('ALTER TABLE car DROP FOREIGN KEY FK_773DE69D2243B88B');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6492243B88B');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BDF59F28F');
        $this->addSql('DROP TABLE car');
        $this->addSql('DROP TABLE car_brand');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE showroom');
        $this->addSql('DROP TABLE user');
    }
}
