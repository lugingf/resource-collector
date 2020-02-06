<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * Перенесено полностью из HostInfo
 * Class AddVmTags
 */
class AddVmTags extends AbstractMigration
{
    private const TAG_TABLE = 'tag';
    private const TAG_RULE_TABLE = 'tag_rule';
    private const HOST2TAG_TABLE = 'host2tag';
    private const RULE2TAG_TABLE = 'rule2tag';

    public function up()
    {
        $this->createTagTable();
        $this->createTagRuleTable();
        $this->createHost2TagTable();
        $this->createRule2TagTable();
    }

    public function down()
    {
        // нет отката
    }

    protected function createTagTable()
    {
        if ($this->hasTable(self::TAG_TABLE)) {
            return;
        }
        $table = $this->table(
            self::TAG_TABLE,
            [
                'id' => false,
                'primary_key' => ['id'],
            ]
        );
        $table
            ->addColumn('id', 'integer', ['limit' => 11, 'null' => false, 'identity' => true])
            ->addColumn('name', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('value', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->create();
    }

    protected function createTagRuleTable()
    {
        if ($this->hasTable(self::TAG_RULE_TABLE)) {
            return;
        }
        $this->table(self::TAG_RULE_TABLE)
            ->addColumn('name', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('type', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('body', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => false, 'default' => ''])
            ->addColumn('priority', 'integer', ['limit' => 3, 'null' => false])
            ->addColumn('comment', 'string', ['length' => 255, 'null' => true, 'default' => ''])
            ->addIndex(['name'], ['name' => 'ux_name', 'unique' => true])
            ->create();
    }

    private function createHost2TagTable()
    {
        if ($this->hasTable(self::HOST2TAG_TABLE)) {
            return;
        }
        $this->table(self::HOST2TAG_TABLE)
            ->addColumn('host_name', 'char', ['length' => 255, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('rule_id', 'integer', ['limit' => 11, 'null' => false])
            ->addIndex(['host_name'], ['name' => 'ix_host_name'])
            ->create();
    }

    private function createRule2TagTable()
    {
        if ($this->hasTable(self::RULE2TAG_TABLE)) {
            return;
        }
        $this->table(self::RULE2TAG_TABLE)
            ->addColumn('rule_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addIndex(['rule_id', 'tag_id'], ['name' => 'ux_rule_tag', 'unique' => true])
            ->create();
    }
}
