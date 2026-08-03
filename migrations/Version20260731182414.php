<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260731182414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the equipment catalogue and its decimal pricing rates.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, pricing_model VARCHAR(20) NOT NULL, category VARCHAR(30) NOT NULL, INDEX idx_equipment_category (category), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pricing_rate (id INT AUTO_INCREMENT NOT NULL, duration_in_days INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, equipment_id INT NOT NULL, INDEX idx_pricing_rate_equipment (equipment_id), UNIQUE INDEX uniq_pricing_rate_duration (equipment_id, duration_in_days), PRIMARY KEY (id), CONSTRAINT chk_pricing_rate_duration CHECK (duration_in_days IS NULL OR duration_in_days > 0), CONSTRAINT chk_pricing_rate_amount CHECK (amount >= 0)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE pricing_rate ADD CONSTRAINT FK_F4FA3587517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pricing_rate DROP FOREIGN KEY FK_F4FA3587517FE9FE');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE pricing_rate');
    }
}
