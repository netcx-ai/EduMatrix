<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AddAiFieldsToContentLibrary extends Migrator
{
    public function change()
    {
        $table = $this->table('content_library');
        
        // 检查字段是否存在，避免重复添加
        if (!$table->hasColumn('source_type')) {
            $table->addColumn('source_type', 'string', [
                'limit' => 20,
                'default' => 'upload',
                'comment' => '来源类型：upload上传, ai_generateAI生成'
            ]);
        }
        
        if (!$table->hasColumn('ai_tool_code')) {
            $table->addColumn('ai_tool_code', 'string', [
                'limit' => 50,
                'null' => true,
                'comment' => 'AI工具编码（如果是AI生成）'
            ]);
        }
        
        $table->update();
    }
} 