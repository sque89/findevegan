<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180401150453 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE blog (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, slug VARCHAR(100) NOT NULL, url VARCHAR(500) NOT NULL, feed VARCHAR(500) NOT NULL, title VARCHAR(100) NOT NULL, email VARCHAR(100) DEFAULT NULL, raw TINYINT(1) NOT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, isbn10 VARCHAR(10) DEFAULT NULL, isbn13 VARCHAR(13) DEFAULT NULL, title VARCHAR(500) NOT NULL, pages INT DEFAULT NULL, released DATETIME DEFAULT NULL, asin VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe (id INT AUTO_INCREMENT NOT NULL, blog_id INT DEFAULT NULL, title VARCHAR(500) NOT NULL, permalink VARCHAR(190) NOT NULL, image VARCHAR(16) DEFAULT NULL, image_orientation VARCHAR(10) DEFAULT NULL, enabled TINYINT(1) NOT NULL, released DATETIME NOT NULL, crawled DATETIME NOT NULL, UNIQUE INDEX UNIQ_DA88B137F286BC32 (permalink), UNIQUE INDEX UNIQ_DA88B137C53D045F (image), INDEX IDX_DA88B137DAE07E97 (blog_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipes_recipe_categories (recipe_id INT NOT NULL, recipe_catetory_id INT NOT NULL, INDEX IDX_A80C3E8159D8A214 (recipe_id), INDEX IDX_A80C3E81908255D5 (recipe_catetory_id), PRIMARY KEY(recipe_id, recipe_catetory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, slug VARCHAR(50) NOT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_category_alternative (id INT AUTO_INCREMENT NOT NULL, recipe_category_id INT DEFAULT NULL, slug VARCHAR(50) NOT NULL, INDEX IDX_FEB26A2FC6B4D386 (recipe_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE searchterm (id INT AUTO_INCREMENT NOT NULL, term VARCHAR(100) NOT NULL, first_search DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', latest_search DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137DAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id)');
        $this->addSql('ALTER TABLE recipes_recipe_categories ADD CONSTRAINT FK_A80C3E8159D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipes_recipe_categories ADD CONSTRAINT FK_A80C3E81908255D5 FOREIGN KEY (recipe_catetory_id) REFERENCES recipe_category (id)');
        $this->addSql('ALTER TABLE recipe_category_alternative ADD CONSTRAINT FK_FEB26A2FC6B4D386 FOREIGN KEY (recipe_category_id) REFERENCES recipe_category (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137DAE07E97');
        $this->addSql('ALTER TABLE recipes_recipe_categories DROP FOREIGN KEY FK_A80C3E8159D8A214');
        $this->addSql('ALTER TABLE recipes_recipe_categories DROP FOREIGN KEY FK_A80C3E81908255D5');
        $this->addSql('ALTER TABLE recipe_category_alternative DROP FOREIGN KEY FK_FEB26A2FC6B4D386');
        $this->addSql('DROP TABLE blog');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE recipe');
        $this->addSql('DROP TABLE recipes_recipe_categories');
        $this->addSql('DROP TABLE recipe_category');
        $this->addSql('DROP TABLE recipe_category_alternative');
        $this->addSql('DROP TABLE searchterm');
    }
}
