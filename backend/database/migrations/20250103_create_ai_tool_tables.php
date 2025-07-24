<?php

use think\migration\Migrator;
use think\migration\db\Column;

class CreateAiToolTables extends Migrator
{
    public function change()
    {
        // 创建AI工具配置表
        $this->createAiToolTable();
        
        // 创建AI工具使用记录表
        $this->createAiUsageTable();
        
        // 插入默认AI工具配置
        $this->insertDefaultAiTools();
    }
    
    /**
     * 创建AI工具配置表
     */
    private function createAiToolTable()
    {
        $table = $this->table('edu_ai_tool', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('name', 'string', ['limit' => 100, 'null' => false, 'comment' => '工具名称'])
              ->addColumn('code', 'string', ['limit' => 50, 'null' => false, 'comment' => '工具编码'])
              ->addColumn('description', 'text', ['null' => true, 'comment' => '工具描述'])
              ->addColumn('category', 'string', ['limit' => 50, 'null' => false, 'comment' => '分类：content内容生成,analysis分析,assessment评估'])
              ->addColumn('prompt_template', 'text', ['null' => true, 'comment' => '提示词模板'])
              ->addColumn('api_config', 'json', ['null' => true, 'comment' => 'API配置信息'])
              ->addColumn('icon', 'string', ['limit' => 255, 'null' => true, 'comment' => '图标'])
              ->addColumn('sort', 'integer', ['limit' => 11, 'default' => 0, 'comment' => '排序'])
              ->addColumn('status', 'integer', ['limit' => 1, 'default' => 1, 'comment' => '状态：0禁用，1启用'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addColumn('update_time', 'datetime', ['null' => true, 'comment' => '更新时间'])
              ->addIndex(['code'], ['unique' => true])
              ->addIndex(['category'])
              ->addIndex(['status'])
              ->create();
    }
    
    /**
     * 创建AI工具使用记录表
     */
    private function createAiUsageTable()
    {
        $table = $this->table('edu_ai_usage', ['engine' => 'InnoDB', 'collation' => 'utf8mb4_unicode_ci']);
        $table->addColumn('tool_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '工具ID'])
              ->addColumn('user_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '用户ID'])
              ->addColumn('school_id', 'integer', ['limit' => 11, 'null' => false, 'comment' => '学校ID'])
              ->addColumn('request_data', 'json', ['null' => true, 'comment' => '请求数据'])
              ->addColumn('response_data', 'json', ['null' => true, 'comment' => '响应数据'])
              ->addColumn('status', 'string', ['limit' => 20, 'default' => 'success', 'comment' => '状态：success成功,failed失败'])
              ->addColumn('error_message', 'text', ['null' => true, 'comment' => '错误信息'])
              ->addColumn('create_time', 'datetime', ['null' => true, 'comment' => '创建时间'])
              ->addIndex(['tool_id'])
              ->addIndex(['user_id'])
              ->addIndex(['school_id'])
              ->addIndex(['status'])
              ->addIndex(['create_time'])
              ->create();
    }
    
    /**
     * 插入默认AI工具配置
     */
    private function insertDefaultAiTools()
    {
        $tools = [
            [
                'name' => '讲稿生成',
                'code' => 'lecture_generator',
                'description' => '根据课程主题和内容自动生成教学讲稿',
                'category' => 'content',
                'prompt_template' => '请根据以下课程信息生成一份详细的教学讲稿：\n课程主题：{topic}\n课程目标：{objectives}\n学生年级：{grade}\n课程时长：{duration}分钟\n\n要求：\n1. 结构清晰，逻辑性强\n2. 语言通俗易懂\n3. 包含互动环节\n4. 适合{grade}年级学生理解',
                'api_config' => json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'max_tokens' => 2000,
                    'temperature' => 0.7
                ]),
                'icon' => '📝',
                'sort' => 1,
                'status' => 1
            ],
            [
                'name' => '作业生成',
                'code' => 'homework_generator',
                'description' => '根据课程内容自动生成练习题和作业',
                'category' => 'content',
                'prompt_template' => '请根据以下课程内容生成一份作业：\n课程内容：{content}\n知识点：{knowledge_points}\n难度要求：{difficulty}\n题目数量：{question_count}道\n\n要求：\n1. 题目类型多样（选择题、填空题、简答题等）\n2. 难度适中，符合{difficulty}要求\n3. 包含答案和解析\n4. 覆盖主要知识点',
                'api_config' => json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'max_tokens' => 1500,
                    'temperature' => 0.6
                ]),
                'icon' => '📝',
                'sort' => 2,
                'status' => 1
            ],
            [
                'name' => '题库生成',
                'code' => 'question_bank_generator',
                'description' => '根据知识点自动生成题库',
                'category' => 'content',
                'prompt_template' => '请根据以下知识点生成题库：\n知识点：{knowledge_points}\n学科：{subject}\n年级：{grade}\n题目类型：{question_types}\n题目数量：{question_count}道\n\n要求：\n1. 题目质量高，符合教学标准\n2. 包含多种题型\n3. 提供详细答案和解析\n4. 难度分布合理',
                'api_config' => json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'max_tokens' => 2500,
                    'temperature' => 0.5
                ]),
                'icon' => '📚',
                'sort' => 3,
                'status' => 1
            ],
            [
                'name' => '课程分析',
                'code' => 'course_analysis',
                'description' => '分析课程内容和教学效果',
                'category' => 'analysis',
                'prompt_template' => '请对以下课程进行分析：\n课程内容：{content}\n教学目标：{objectives}\n学生反馈：{feedback}\n\n请从以下方面进行分析：\n1. 课程结构合理性\n2. 教学目标的达成度\n3. 学生理解程度\n4. 改进建议',
                'api_config' => json_encode([
                    'model' => 'gpt-3.5-turbo',
                    'max_tokens' => 1500,
                    'temperature' => 0.3
                ]),
                'icon' => '📈',
                'sort' => 4,
                'status' => 1
            ]
        ];
        
        foreach ($tools as $tool) {
            $this->insert('edu_ai_tool', $tool);
        }
    }
} 