<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

final class Version20201202164654 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add converter table';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->createTable('converter');

        $table->addColumn(
            'id',
            Types::INTEGER,
            ['autoincrement' => true]
        );
        $table->setPrimaryKey(['id']);

        $table->addColumn(
            'name',
            Types::STRING,
            ['notnull' => true, 'length' => 255, 'unique' => true]
        );
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('converter');
    }
}
