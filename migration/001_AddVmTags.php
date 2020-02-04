<?php

use Phinx\Migration\AbstractMigration;

/**
 * Перенесено полностью из HostInfo
 * Class AddVmTags
 */
class AddVmTags extends AbstractMigration
{
    private const RM_TAG_TABLE = 'rm_tag';
    private const RM_TAG_RULE_TABLE = 'rm_tag_rule';
    private const RM_HOST2TAG_TABLE = 'rm_host2tag';
    private const RM_RULE2TAG_TABLE = 'rm_rule2tag';

    public function up()
    {
        $this->createTagTable();
        $this->createRmTagRuleTable();
        $this->createHost2TagTable();
        $this->createRule2TagTable();
    }

    public function down()
    {
        // нет отката
    }

    protected function createTagTable()
    {
        if ($this->hasTable(self::RM_TAG_TABLE)) {
            return;
        }
        $table = $this->table(
            self::RM_TAG_TABLE,
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

    protected function createRmTagRuleTable()
    {
        if ($this->hasTable(self::RM_TAG_RULE_TABLE)) {
            return;
        }
        $this->table(self::RM_TAG_RULE_TABLE)
            ->addColumn('name', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('type', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('body', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('priority', 'integer', ['limit' => 3, 'null' => false])
            ->addColumn('comment', 'string', ['length' => 255, 'null' => true, 'default' => ''])
            ->addIndex(['name'], ['name' => 'ux_name', 'unique' => true])
            ->create();
    }

    private function createHost2TagTable()
    {
        if ($this->hasTable(self::RM_HOST2TAG_TABLE)) {
            return;
        }
        $this->table(self::RM_HOST2TAG_TABLE)
            ->addColumn('host_name', 'char', ['length' => 255, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('rule_id', 'integer', ['limit' => 11, 'null' => false])
            ->addIndex(['host_name'], ['name' => 'ix_host_name'])
            ->create();
    }

    private function createRule2TagTable()
    {
        if ($this->hasTable(self::RM_RULE2TAG_TABLE)) {
            return;
        }
        $this->table(self::RM_RULE2TAG_TABLE)
            ->addColumn('rule_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addIndex(['rule_id', 'tag_id'], ['name' => 'ux_rule_tag', 'unique' => true])
            ->create();
    }
}
