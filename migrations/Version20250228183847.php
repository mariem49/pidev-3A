<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250228183847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B8EE38726C6E55B5 ON club (nom)');
        $this->addSql('DROP INDEX fk_club_user ON club');
        $this->addSql('CREATE INDEX IDX_B8EE3872A76ED395 ON club (user_id)');
        $this->addSql('ALTER TABLE cour CHANGE duree duree INT NOT NULL');
        $this->addSql('ALTER TABLE cour ADD CONSTRAINT FK_A71F964FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX fk_cour_user ON cour');
        $this->addSql('CREATE INDEX IDX_A71F964FA76ED395 ON cour (user_id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E61190A32 FOREIGN KEY (club_id) REFERENCES club (id)');
        $this->addSql('CREATE INDEX IDX_B26681E61190A32 ON evenement (club_id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DDAE07E97 FOREIGN KEY (blog_id) REFERENCES blog (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE seance CHANGE date date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE seance ADD CONSTRAINT FK_DF7DFD0EB7942F03 FOREIGN KEY (cour_id) REFERENCES cour (id)');
        $this->addSql('CREATE INDEX IDX_DF7DFD0EB7942F03 ON seance (cour_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872A76ED395');
        $this->addSql('DROP INDEX UNIQ_B8EE38726C6E55B5 ON club');
        $this->addSql('ALTER TABLE club DROP FOREIGN KEY FK_B8EE3872A76ED395');
        $this->addSql('DROP INDEX idx_b8ee3872a76ed395 ON club');
        $this->addSql('CREATE INDEX fk_club_user ON club (user_id)');
        $this->addSql('ALTER TABLE club ADD CONSTRAINT FK_B8EE3872A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cour DROP FOREIGN KEY FK_A71F964FA76ED395');
        $this->addSql('ALTER TABLE cour DROP FOREIGN KEY FK_A71F964FA76ED395');
        $this->addSql('ALTER TABLE cour CHANGE duree duree VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX idx_a71f964fa76ed395 ON cour');
        $this->addSql('CREATE INDEX fk_cour_user ON cour (user_id)');
        $this->addSql('ALTER TABLE cour ADD CONSTRAINT FK_A71F964FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E61190A32');
        $this->addSql('DROP INDEX IDX_B26681E61190A32 ON evenement');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DDAE07E97');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE seance DROP FOREIGN KEY FK_DF7DFD0EB7942F03');
        $this->addSql('DROP INDEX IDX_DF7DFD0EB7942F03 ON seance');
        $this->addSql('ALTER TABLE seance CHANGE date date DATE NOT NULL');
    }
}
