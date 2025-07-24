<?php
namespace app\service\ai;

class MockProvider implements AiProviderInterface
{
    public function generate(string $toolCode, array $params): string
    {
        // 根据工具代码生成模拟内容
        $responses = [
            'lecture_generator' => $this->generateLecture($params),
            'homework_generator' => $this->generateHomework($params),
            'question_bank_generator' => $this->generateQuestionBank($params),
            'course_analysis' => $this->generateCourseAnalysis($params),
            'lesson_plan' => $this->generateLessonPlan($params),
            'teaching_reflection' => $this->generateTeachingReflection($params)
        ];
        
        return $responses[$toolCode] ?? 'AI工具响应内容';
    }
    
    private function generateLecture(array $params): string
    {
        $topic = $params['topic'] ?? '未知主题';
        $objectives = $params['objectives'] ?? '教学目标';
        $grade = $params['grade'] ?? '年级';
        $duration = $params['duration'] ?? '45分钟';
        
        return "# {$topic} 教学讲稿\n\n" .
               "## 教学目标\n" .
               "{$objectives}\n\n" .
               "## 教学对象\n" .
               "{$grade}学生\n\n" .
               "## 教学时长\n" .
               "{$duration}\n\n" .
               "## 教学过程\n\n" .
               "### 1. 导入新课 (5分钟)\n" .
               "- 通过生活实例引入主题\n" .
               "- 激发学生学习兴趣\n\n" .
               "### 2. 新课讲解 (25分钟)\n" .
               "- 核心概念讲解\n" .
               "- 示例演示\n" .
               "- 互动讨论\n\n" .
               "### 3. 练习巩固 (10分钟)\n" .
               "- 课堂练习\n" .
               "- 小组讨论\n\n" .
               "### 4. 总结归纳 (5分钟)\n" .
               "- 知识要点总结\n" .
               "- 布置作业\n\n" .
               "## 教学反思\n" .
               "本节课通过多种教学方法，帮助学生掌握核心知识点，教学效果良好。";
    }
    
    private function generateHomework(array $params): string
    {
        $content = $params['content'] ?? '课程内容';
        $difficulty = $params['difficulty'] ?? '中等';
        $questionCount = $params['question_count'] ?? 5;
        
        return "# 课后作业\n\n" .
               "## 作业要求\n" .
               "基于课程内容：{$content}\n" .
               "难度要求：{$difficulty}\n" .
               "题目数量：{$questionCount}道\n\n" .
               "## 作业题目\n\n" .
               "### 选择题\n" .
               "1. 题目内容...\n" .
               "   A. 选项A\n" .
               "   B. 选项B\n" .
               "   C. 选项C\n" .
               "   D. 选项D\n\n" .
               "### 填空题\n" .
               "2. 填空题内容：_____\n\n" .
               "### 简答题\n" .
               "3. 简答题内容，请详细回答。\n\n" .
               "## 参考答案\n" .
               "1. 答案：A\n" .
               "2. 答案：具体内容\n" .
               "3. 答案：详细解答...";
    }
    
    private function generateQuestionBank(array $params): string
    {
        $knowledgePoints = $params['knowledge_points'] ?? '知识点';
        $subject = $params['subject'] ?? '学科';
        $grade = $params['grade'] ?? '年级';
        
        return "# {$subject}题库 - {$knowledgePoints}\n\n" .
               "## 题库信息\n" .
               "- 学科：{$subject}\n" .
               "- 年级：{$grade}\n" .
               "- 知识点：{$knowledgePoints}\n\n" .
               "## 题目列表\n\n" .
               "### 选择题 (10道)\n" .
               "1. 选择题1...\n" .
               "2. 选择题2...\n" .
               "...\n\n" .
               "### 填空题 (8道)\n" .
               "11. 填空题1...\n" .
               "12. 填空题2...\n" .
               "...\n\n" .
               "### 简答题 (5道)\n" .
               "19. 简答题1...\n" .
               "20. 简答题2...\n" .
               "...\n\n" .
               "## 答案与解析\n" .
               "每道题都配有详细的答案和解析，帮助学生理解。";
    }
    
    private function generateCourseAnalysis(array $params): string
    {
        $content = $params['content'] ?? '课程内容';
        $objectives = $params['objectives'] ?? '教学目标';
        
        return "# 课程分析报告\n\n" .
               "## 课程基本信息\n" .
               "- 课程内容：{$content}\n" .
               "- 教学目标：{$objectives}\n\n" .
               "## 分析结果\n\n" .
               "### 1. 课程结构分析\n" .
               "- 结构合理性：良好\n" .
               "- 逻辑性：清晰\n" .
               "- 完整性：完整\n\n" .
               "### 2. 教学目标达成度\n" .
               "- 知识目标：90%\n" .
               "- 能力目标：85%\n" .
               "- 情感目标：80%\n\n" .
               "### 3. 学生理解程度\n" .
               "- 优秀：30%\n" .
               "- 良好：45%\n" .
               "- 一般：20%\n" .
               "- 需改进：5%\n\n" .
               "## 改进建议\n" .
               "1. 增加互动环节\n" .
               "2. 优化教学节奏\n" .
               "3. 加强重点内容讲解";
    }
    
    private function generateLessonPlan(array $params): string
    {
        $courseName = $params['course_name'] ?? '课程名称';
        $objectives = $params['objectives'] ?? '教学目标';
        $keyPoints = $params['key_points'] ?? '教学重点';
        $duration = $params['duration'] ?? '45分钟';
        
        return "# {$courseName} 教案\n\n" .
               "## 基本信息\n" .
               "- 课程名称：{$courseName}\n" .
               "- 教学时长：{$duration}\n\n" .
               "## 教学目标\n" .
               "{$objectives}\n\n" .
               "## 教学重点\n" .
               "{$keyPoints}\n\n" .
               "## 教学难点\n" .
               "需要重点突破的知识点\n\n" .
               "## 教学过程\n\n" .
               "### 第一环节：导入 (5分钟)\n" .
               "**活动设计：**\n" .
               "- 情境导入\n" .
               "- 问题引导\n\n" .
               "### 第二环节：新课讲解 (25分钟)\n" .
               "**活动设计：**\n" .
               "- 概念讲解\n" .
               "- 示例演示\n" .
               "- 互动讨论\n\n" .
               "### 第三环节：练习巩固 (10分钟)\n" .
               "**活动设计：**\n" .
               "- 课堂练习\n" .
               "- 小组合作\n\n" .
               "### 第四环节：总结 (5分钟)\n" .
               "**活动设计：**\n" .
               "- 知识梳理\n" .
               "- 作业布置\n\n" .
               "## 教学反思\n" .
               "本节课的教学效果良好，学生参与度高，教学目标基本达成。";
    }
    
    private function generateTeachingReflection(array $params): string
    {
        $content = $params['content'] ?? '教学内容';
        $process = $params['process'] ?? '教学过程';
        $performance = $params['performance'] ?? '学生表现';
        
        return "# 教学反思\n\n" .
               "## 教学基本信息\n" .
               "- 教学内容：{$content}\n" .
               "- 教学过程：{$process}\n" .
               "- 学生表现：{$performance}\n\n" .
               "## 教学反思\n\n" .
               "### 本节课的优点\n" .
               "1. 教学设计合理，符合学生认知规律\n" .
               "2. 教学方法多样，学生参与度高\n" .
               "3. 课堂氛围活跃，学习效果好\n\n" .
               "### 需要改进的地方\n" .
               "1. 时间分配可以更加合理\n" .
               "2. 个别学生注意力不够集中\n" .
               "3. 练习环节可以更加丰富\n\n" .
               "### 下次教学建议\n" .
               "1. 优化时间安排，确保重点内容有充足时间\n" .
               "2. 增加更多互动环节，提高学生参与度\n" .
               "3. 设计更多样化的练习形式\n\n" .
               "## 总体评价\n" .
               "本节课整体效果良好，教学目标基本达成，学生反馈积极。";
    }
} 