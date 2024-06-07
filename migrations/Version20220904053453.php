<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220904053453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add nbre_playlist table';
    }

    public function up(Schema $schema) : void
    {
        // Créer la table nbre_playlist
        $table = $schema->createTable('nbre_playlist');
        $table->addColumn('id', 'integer');
        $table->addColumn('nombre', 'integer');
        
        // Définir la clé primaire
        $table->setPrimaryKey(['id']);
        
        // Définir la clé étrangère vers la table playlist
        $table->addForeignKeyConstraint('playlist', ['id'], ['id'], [], 'fk_playlist_id');
    }

    public function down(Schema $schema) : void
    {
        // Supprimer la table nbre_playlist si nécessaire
        $schema->dropTable('nbre_playlist');
    }
}
