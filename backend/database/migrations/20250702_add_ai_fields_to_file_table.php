<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddAiFieldsToFileTable extends Migrator
{
    /**
     * 添加AI生成相关字段到文件表
     */
    public function change()
    {
        $table = $this->table('file');
        
        // 添加AI工具代码字段
        if (!$table->hasColumn('ai_tool_code')) {
            $table->addColumn('ai_tool_code', 'string', [
                'limit' => 50,
                'null' => true,
                'comment' => 'AI工具代码（如果是AI生成的文件）',
                'after' => 'course_id'
            ]);
        }
        
        // 添加关联内容ID字段
        if (!$table->hasColumn('content_id')) {
            $table->addColumn('content_id', 'integer', [
                'null' => true,
                'comment' => '关联的内容库ID',
                'after' => 'ai_tool_code'
            ]);
        }
        
        // 添加来源类型字段
        if (!$table->hasColumn('source_type')) {
            $table->addColumn('source_type', 'string', [
                'limit' => 20,
                'default' => 'upload',
                'comment' => '来源类型：upload上传，ai_generateAI生成',
                'after' => 'content_id'
            ]);
        }
        
        // 添加索引（检查索引是否存在）
        $indexes = $table->getIndexes();
        if (!isset($indexes['idx_ai_tool_code'])) {
            $table->addIndex(['ai_tool_code'], ['name' => 'idx_ai_tool_code']);
        }
        if (!isset($indexes['idx_content_id'])) {
            $table->addIndex(['content_id'], ['name' => 'idx_content_id']);
        }
        if (!isset($indexes['idx_source_type'])) {
            $table->addIndex(['source_type'], ['name' => 'idx_source_type']);
        }
        
        $table->update();
    }
} 