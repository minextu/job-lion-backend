<?php

namespace JobLion\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180501160001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE jobCategories (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, created DATETIME NOT NULL, UNIQUE INDEX UNIQ_FF820BB45E237E06 (name), INDEX IDX_FF820BB4A76ED395 (user_id), INDEX name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(100) NOT NULL, firstName VARCHAR(255) NOT NULL, lastName VARCHAR(255) NOT NULL, hash VARCHAR(100) NOT NULL, created DATETIME NOT NULL, activated TINYINT(1) NOT NULL, activationCode VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX email (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experienceReports (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, company_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, created DATETIME NOT NULL, INDEX IDX_33269E77A76ED395 (user_id), INDEX IDX_33269E77979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE experiencereport_jobcategory (experiencereport_id INT NOT NULL, jobcategory_id INT NOT NULL, INDEX IDX_5A4431F37B563823 (experiencereport_id), INDEX IDX_5A4431F31B7AFD87 (jobcategory_id), PRIMARY KEY(experiencereport_id, jobcategory_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, text VARCHAR(255) NOT NULL, created DATETIME NOT NULL, experienceReport_id INT DEFAULT NULL, INDEX IDX_5F9E962AA76ED395 (user_id), INDEX IDX_5F9E962AF9A7BA80 (experienceReport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE companies (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, title VARCHAR(100) NOT NULL, created DATETIME NOT NULL, experienceReports_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8244AA3A2B36786B (title), INDEX IDX_8244AA3AA76ED395 (user_id), INDEX IDX_8244AA3AA3099BF (experienceReports_id), INDEX title (title), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE jobCategories ADD CONSTRAINT FK_FF820BB4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE experienceReports ADD CONSTRAINT FK_33269E77A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE experienceReports ADD CONSTRAINT FK_33269E77979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE experiencereport_jobcategory ADD CONSTRAINT FK_5A4431F37B563823 FOREIGN KEY (experiencereport_id) REFERENCES experienceReports (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experiencereport_jobcategory ADD CONSTRAINT FK_5A4431F31B7AFD87 FOREIGN KEY (jobcategory_id) REFERENCES jobCategories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF9A7BA80 FOREIGN KEY (experienceReport_id) REFERENCES experienceReports (id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3AA3099BF FOREIGN KEY (experienceReports_id) REFERENCES experienceReports (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE experiencereport_jobcategory DROP FOREIGN KEY FK_5A4431F31B7AFD87');
        $this->addSql('ALTER TABLE jobCategories DROP FOREIGN KEY FK_FF820BB4A76ED395');
        $this->addSql('ALTER TABLE experienceReports DROP FOREIGN KEY FK_33269E77A76ED395');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA76ED395');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3AA76ED395');
        $this->addSql('ALTER TABLE experiencereport_jobcategory DROP FOREIGN KEY FK_5A4431F37B563823');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AF9A7BA80');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3AA3099BF');
        $this->addSql('ALTER TABLE experienceReports DROP FOREIGN KEY FK_33269E77979B1AD6');
        $this->addSql('DROP TABLE jobCategories');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE experienceReports');
        $this->addSql('DROP TABLE experiencereport_jobcategory');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE companies');
    }
}
