<?php
namespace app\model;

use think\Model;

/**
 * AI工具配置模型
 */
class AiTool extends Model
{
    protected $name = 'ai_tool';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'api_config' => 'json',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 分类常量
    const CATEGORY_CONTENT = 'content';      // 内容生成
    const CATEGORY_ANALYSIS = 'analysis';    // 分析
    const CATEGORY_ASSESSMENT = 'assessment'; // 评估
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
    /**
     * 获取分类列表
     */
    public static function getCategoryList()
    {
        return [
            self::CATEGORY_CONTENT => '内容生成',
            self::CATEGORY_ANALYSIS => '分析',
            self::CATEGORY_ASSESSMENT => '评估'
        ];
    }
    
    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用'
        ];
    }
    
    /**
     * 获取分类名称
     */
    public function getCategoryTextAttr($value, $data)
    {
        $categories = self::getCategoryList();
        return isset($categories[$data['category']]) ? $categories[$data['category']] : '';
    }
    
    /**
     * 获取状态名称
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = self::getStatusList();
        return isset($statuses[$data['status']]) ? $statuses[$data['status']] : '';
    }
    
    /**
     * 关联使用记录
     */
    public function usageRecords()
    {
        return $this->hasMany(AiUsage::class, 'tool_id', 'id');
    }
    
    /**
     * 关联学校权限
     */
    public function schoolPermissions()
    {
        return $this->hasMany(AiToolSchool::class, 'tool_id', 'id');
    }
    
    /**
     * 获取启用状态的工具列表
     */
    public static function getEnabledTools($category = null)
    {
        $query = self::where('status', self::STATUS_ENABLED);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        return $query->order('sort ASC, id ASC')->select();
    }
    
    /**
     * 根据编码获取工具
     */
    public static function getByCode($code)
    {
        return self::where('code', $code)
                  ->where('status', self::STATUS_ENABLED)
                  ->find();
    }
    
    /**
     * 获取工具统计信息
     */
    public function getStatistics()
    {
        $usageCount = $this->usageRecords()->count();
        $todayUsage = $this->usageRecords()
                          ->whereTime('create_time', 'today')
                          ->count();
        $monthUsage = $this->usageRecords()
                          ->whereTime('create_time', 'month')
                          ->count();
        
        return [
            'total_usage' => $usageCount,
            'today_usage' => $todayUsage,
            'month_usage' => $monthUsage
        ];
    }
} 