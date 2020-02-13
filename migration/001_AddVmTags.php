<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;
use RMS\ResourceCollector\Model\Tag;
use RMS\ResourceCollector\Model\TagRule;
use RMS\ResourceCollector\TagRules\Unit2TagLinker;
use RMS\ResourceCollector\TagRules\Rule2TagLinker;

/**
 * Перенесено полностью из HostInfo
 * Class AddVmTags
 */
class AddVmTags extends AbstractMigration
{
    public function up()
    {
        $this->createTagTable();
        $this->createTagRuleTable();
        $this->createUnit2TagTable();
        $this->createRule2TagTable();
    }

    public function down()
    {
        // нет отката
    }

    protected function createTagTable()
    {
        if ($this->hasTable(Tag::TABLE_NAME)) {
            return;
        }
        $table = $this->table(
            Tag::TABLE_NAME,
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
        if ($this->hasTable(TagRule::TABLE_NAME)) {
            return;
        }
        $this->table(TagRule::TABLE_NAME)
            ->addColumn('name', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('type', 'char', ['length' => 255, 'null' => false, 'default' => ''])
            ->addColumn('body', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => false, 'default' => ''])
            ->addColumn('priority', 'integer', ['limit' => 3, 'null' => false])
            ->addColumn('comment', 'string', ['length' => 255, 'null' => true, 'default' => ''])
            ->addIndex(['name'], ['name' => 'ux_rule_name', 'unique' => true])
            ->create();
    }

    private function createUnit2TagTable()
    {
        if ($this->hasTable(Unit2TagLinker::TABLE)) {
            return;
        }
        $this->table(Unit2TagLinker::TABLE)
            ->addColumn('unit_name', 'char', ['length' => 255, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('rule_id', 'integer', ['limit' => 11, 'null' => false])
            ->addIndex(['unit_name'], ['name' => 'ix_unit_name'])
            ->create();
    }

    private function createRule2TagTable()
    {
        if ($this->hasTable(Rule2TagLinker::TABLE)) {
            return;
        }
        $this->table(Rule2TagLinker::TABLE)
            ->addColumn('rule_id', 'integer', ['length' => 11, 'null' => false])
            ->addColumn('tag_id', 'integer', ['length' => 11, 'null' => false])
            ->addIndex(['rule_id', 'tag_id'], ['name' => 'ux_rule_tag', 'unique' => true])
            ->create();
    }
}
