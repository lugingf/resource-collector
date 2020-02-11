<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use RMS\ResourceCollector\Model\Item;
use RMS\ResourceCollector\Model\Unit;

class InitResourceTables extends AbstractMigration
{
    public function up()
    {
        $this->createUnitTable();
        $this->createItemTable();
    }

    private function createUnitTable()
    {
        if ($this->hasTable(Unit::TABLE_NAME)) {
            return;
        }

        $table = $this->table(Unit::TABLE_NAME, ["id" => false]);

        $table->addColumn('source', 'string', ['null' => false])
            ->addColumn('type', 'string', ['length' => 255, 'null' => false])
            ->addColumn('name', 'string', ['length' => 255, 'null' => false])
            ->addColumn('properties', 'text')
            ->addIndex('name', ['name' => 'ux_unit_name', 'unique' => true])
            ->addIndex('source', ['name' => 'ix_source'])
            ->create();
    }

    private function createItemTable()
    {
        if ($this->hasTable(Item::TABLE_NAME)) {
            return;
        }

        $table = $this->table(Item::TABLE_NAME, ["id" => false]);

        $table->addColumn('unit_name', 'string', ['length' => 255, 'null' => false])
            ->addColumn('type', 'string', ['length' => 255, 'null' => false])
            ->addColumn('amount', 'string', ['length' => 255, 'null' => false])
            ->addColumn('properties', 'text')
            ->addIndex('unit_name', ['name' => 'ix_unit'])
            ->create();
    }

    public function down()
    {
        // не нужно
    }
}