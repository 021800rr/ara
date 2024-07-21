<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240604122011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE refresh_tokens_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('CREATE TABLE refresh_tokens (
                            id INT NOT NULL,
                            refresh_token VARCHAR(128) NOT NULL,
                            username VARCHAR(255) NOT NULL,
                            valid TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                            PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9BACE7E1C74F2195 ON refresh_tokens (refresh_token)');


        $this->addSql('CREATE TABLE "user" (
                            id INT NOT NULL,
                            name VARCHAR(200) NOT NULL,
                            last_name VARCHAR(200) NOT NULL,
                            email VARCHAR(255) NOT NULL,
                            roles JSON NOT NULL,
                            status VARCHAR(255) NOT NULL,
                            password VARCHAR(255) NOT NULL,
                            PRIMARY KEY(id))'
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');

        $this->addSql('DROP SEQUENCE refresh_tokens_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');

        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE "user"');
    }
}
