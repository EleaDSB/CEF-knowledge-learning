<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds created_at, updated_at, created_by, updated_by audit columns to all entities.
 */
final class Version20260511140921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at, updated_at, created_by, updated_by audit columns to all entities';
    }

    public function up(Schema $schema): void
    {
        // DEFAULT CURRENT_TIMESTAMP backfills existing rows; the application sets the value on new inserts.
        $this->addSql('ALTER TABLE certification ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE cursus ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE lesson_progress ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE purchase ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
        // user already had created_at; only the three remaining audit columns are added
        $this->addSql('ALTER TABLE `user` ADD updated_at DATETIME DEFAULT NULL, ADD created_by VARCHAR(180) DEFAULT NULL, ADD updated_by VARCHAR(180) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE certification DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE cursus DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE lesson DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE lesson_progress DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE purchase DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE theme DROP created_at, DROP updated_at, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE `user` DROP updated_at, DROP created_by, DROP updated_by');
    }
}
