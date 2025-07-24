<?php
namespace app\service;

use app\model\AiTool;
use think\facade\Log;

/**
 * AI工具配置服务类
 * 处理AI工具的配置化参数和动态表单生成
 */
class AiToolConfigService
{
    /**
     * 获取工具的输入参数配置
     * @param string $toolCode 工具编码
     * @return array 输入参数配置
     */
    public static function getInputParams(string $toolCode): array
    {
        try {
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return [];
            }

            $apiConfig = $tool->api_config;
            return $apiConfig['input_params'] ?? [];
        } catch (\Exception $e) {
            Log::error("获取AI工具输入参数失败: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取工具的输出格式配置
     * @param string $toolCode 工具编码
     * @return array 输出格式配置
     */
    public static function getOutputFormat(string $toolCode): array
    {
        try {
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return [];
            }

            $apiConfig = $tool->api_config;
            return $apiConfig['output_format'] ?? [];
        } catch (\Exception $e) {
            Log::error("获取AI工具输出格式失败: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 验证输入参数
     * @param string $toolCode 工具编码
     * @param array $params 用户输入的参数
     * @return array 验证结果
     */
    public static function validateParams(string $toolCode, array $params): array
    {
        $inputParams = self::getInputParams($toolCode);
        $errors = [];
        $validatedParams = [];

        foreach ($inputParams as $param) {
            $name = $param['name'];
            $required = $param['required'] ?? false;
            $type = $param['type'] ?? 'text';

            // 检查必填参数
            if ($required && (!isset($params[$name]) || empty($params[$name]))) {
                $errors[] = "参数 '{$param['label']}' 是必填的";
                continue;
            }

            $valid = true;

            // 如果参数存在，进行类型验证
            if (isset($params[$name])) {
                $value = $params[$name];

                switch ($type) {
                    case 'number':
                        if (!is_numeric($value)) {
                            $errors[] = "参数 '{$param['label']}' 必须是数字";
                            $valid = false;
                            break;
                        }
                        if (isset($param['min']) && $value < $param['min']) {
                            $errors[] = "参数 '{$param['label']}' 不能小于 {$param['min']}";
                            $valid = false;
                            break;
                        }
                        if (isset($param['max']) && $value > $param['max']) {
                            $errors[] = "参数 '{$param['label']}' 不能大于 {$param['max']}";
                            $valid = false;
                            break;
                        }
                        break;
                    case 'select':
                        $options = array_column($param['options'] ?? [], 'value');
                        if (!in_array($value, $options)) {
                            $errors[] = "参数 '{$param['label']}' 的值无效";
                            $valid = false;
                        }
                        break;
                    case 'course_select':
                        // 验证课程ID是否有效，这里需要结合用户权限验证
                        if (!is_numeric($value) || $value <= 0) {
                            $errors[] = "参数 '{$param['label']}' 必须是有效的课程ID";
                            $valid = false;
                        } else {
                            // 这里可以添加更严格的课程权限验证
                            // 但为了避免循环依赖，暂时只做基本验证
                            // 实际权限验证在业务层进行
                        }
                        break;
                    case 'checkbox':
                        if (!is_array($value)) {
                            $errors[] = "参数 '{$param['label']}' 必须是数组";
                            $valid = false;
                            break;
                        }
                        $options = array_column($param['options'] ?? [], 'value');
                        $invalid = false;
                        foreach ($value as $v) {
                            if (!in_array($v, $options)) {
                                $errors[] = "参数 '{$param['label']}' 包含无效值";
                                $invalid = true;
                                break;
                            }
                        }
                        if ($invalid) $valid = false;
                        break;
                }

                if ($valid) {
                    $validatedParams[$name] = $value;
                }
            } else {
                // 设置默认值
                if (isset($param['default'])) {
                    $validatedParams[$name] = $param['default'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'params' => $validatedParams
        ];
    }

    /**
     * 构建系统提示词
     * @param string $toolCode 工具编码
     * @param array $params 用户参数
     * @return string 完整的系统提示词
     */
    public static function buildSystemPrompt(string $toolCode, array $params = []): string
    {
        try {
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return '';
            }

            $apiConfig = $tool->api_config;
            $systemPrompt = $apiConfig['system_prompt'] ?? '';
            $outputFormat = $apiConfig['output_format'] ?? [];

            // 如果有输出格式要求，添加到系统提示词中
            if (!empty($outputFormat)) {
                $formatInstruction = self::buildFormatInstruction($outputFormat);
                $systemPrompt .= "\n\n" . $formatInstruction;
            }

            return $systemPrompt;
        } catch (\Exception $e) {
            Log::error("构建系统提示词失败: " . $e->getMessage());
            return '';
        }
    }

    /**
     * 构建格式说明
     * @param array $outputFormat 输出格式配置
     * @return string 格式说明
     */
    private static function buildFormatInstruction(array $outputFormat): string
    {
        $type = $outputFormat['type'] ?? 'text';
        
        switch ($type) {
            case 'json':
                $schema = $outputFormat['schema'] ?? [];
                $instruction = "请以JSON格式返回内容，包含以下字段:\n";
                $instruction .= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $instruction .= "\n回复中请只包含JSON数据，不要包含其他文本说明。只输出{}部分内容";
                return $instruction;
                
            case 'markdown':
                $structure = $outputFormat['structure'] ?? [];
                $instruction = "请按照以下结构生成内容：\n";
                foreach ($structure as $section) {
                    $instruction .= "- {$section}\n";
                }
                $instruction .= "\n请使用Markdown格式输出，确保结构清晰。";
                return $instruction;
                
            default:
                return "请按照要求生成内容。";
        }
    }

    /**
     * 构建用户提示词
     * @param string $toolCode 工具编码
     * @param array $params 用户参数
     * @return string 用户提示词
     */
    public static function buildUserPrompt(string $toolCode, array $params): string
    {
        try {
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return '';
            }

            $promptTemplate = $tool->prompt_template;
            
            // 替换模板中的占位符
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $value = implode('、', $value);
                }
                $promptTemplate = str_replace("{{$key}}", $value, $promptTemplate);
            }

            return $promptTemplate;
        } catch (\Exception $e) {
            Log::error("构建用户提示词失败: " . $e->getMessage());
            return '';
        }
    }

    /**
     * 获取工具的前端表单配置
     * @param string $toolCode 工具编码
     * @return array 前端表单配置
     */
    public static function getFormConfig(string $toolCode): array
    {
        try {
            $tool = AiTool::getByCode($toolCode);
            if (!$tool) {
                return [];
            }

            $apiConfig = $tool->api_config;
            $inputParams = $apiConfig['input_params'] ?? [];

            // 转换为前端表单配置
            $formConfig = [];
            foreach ($inputParams as $param) {
                $formField = [
                    'name' => $param['name'],
                    'label' => $param['label'],
                    'type' => $param['type'],
                    'required' => $param['required'] ?? false,
                    'placeholder' => $param['placeholder'] ?? '',
                ];

                // 添加类型特定的配置
                switch ($param['type']) {
                    case 'select':
                        $formField['options'] = $param['options'] ?? [];
                        break;
                    case 'number':
                        $formField['min'] = $param['min'] ?? null;
                        $formField['max'] = $param['max'] ?? null;
                        $formField['default'] = $param['default'] ?? null;
                        break;
                    case 'checkbox':
                        $formField['options'] = $param['options'] ?? [];
                        break;
                    case 'course_select':
                        // 课程选择类型，前端根据此类型动态加载课程数据
                        $formField['data_source'] = 'dynamic'; // 标识为动态数据源
                        break;
                }

                $formConfig[] = $formField;
            }

            return $formConfig;
        } catch (\Exception $e) {
            Log::error("获取工具表单配置失败: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 获取所有可用工具的表单配置
     * @param int $schoolId 学校ID
     * @return array 所有工具的表单配置
     */
    public static function getAllToolsFormConfig(int $schoolId): array
    {
        try {
            $tools = AiTool::getEnabledTools();
            $configs = [];

            foreach ($tools as $tool) {
                // 检查学校是否有权限使用该工具
                $hasPermission = $tool->schoolPermissions()
                    ->where('school_id', $schoolId)
                    ->where('status', 1)
                    ->count() > 0;

                if ($hasPermission) {
                    $configs[] = [
                        'tool_id' => $tool->id,
                        'tool_code' => $tool->code,
                        'tool_name' => $tool->name,
                        'tool_description' => $tool->description,
                        'category' => $tool->category,
                        'form_config' => self::getFormConfig($tool->code)
                    ];
                }
            }

            return $configs;
        } catch (\Exception $e) {
            Log::error("获取所有工具表单配置失败: " . $e->getMessage());
            return [];
        }
    }
} 