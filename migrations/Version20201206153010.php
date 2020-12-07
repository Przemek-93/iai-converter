<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201206153010 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add converted_file table';
    }

    public function up(Schema $schema) : void
    {
        $convertedFileTable = $schema->createTable('converted_file');
        $converter = $schema->getTable('converter');

        $convertedFileTable->addColumn(
            'id',
            Types::INTEGER,
            ['autoincrement' => true]
        );
        $convertedFileTable->setPrimaryKey(['id']);

        $convertedFileTable->addColumn(
            'name',
            Types::STRING,
            ['notnull' => true, 'length' => 255]
        );

        $convertedFileTable->addColumn(
            'converter_id',
            Types::INTEGER,
            ['notnull' => true]
        );

        $convertedFileTable->addForeignKeyConstraint(
            $converter,
            ['converter_id'],
            ['id'],
            [],
            'FK_CONVERTER_CONVERTER_FILE'
        );

        $convertedFileTable->addColumn(
            'created_at',
            Types::DATETIME_IMMUTABLE,
            ['notnull' => true]
        );
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('converted_file');
    }
}
