<?php
namespace app\model;

use think\Model;

/**
 * AI工具使用记录模型
 */
class AiUsage extends Model
{
    protected $name = 'ai_usage';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = false;
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'tool_id' => 'integer',
        'user_id' => 'integer',
        'school_id' => 'integer',
        'request_data' => 'json',
        'response_data' => 'json',
        'tokens_used' => 'integer',
        'cost' => 'float',
        'create_time' => 'datetime'
    ];
    
    // 状态常量
    const STATUS_SUCCESS = 'success';  // 成功
    const STATUS_FAILED = 'failed';    // 失败
    
    /**
     * 获取状态列表
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_SUCCESS => '成功',
            self::STATUS_FAILED => '失败'
        ];
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
     * 关联工具
     */
    public function tool()
    {
        return $this->belongsTo(AiTool::class, 'tool_id', 'id');
    }
    
    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    /**
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
    
    /**
     * 获取用户今日使用次数
     */
    public static function getUserTodayUsage($userId, $toolId = null)
    {
        $query = self::where('user_id', $userId)
                    ->whereTime('create_time', 'today');
        
        if ($toolId) {
            $query->where('tool_id', $toolId);
        }
        
        return $query->count();
    }
    
    /**
     * 获取用户本月使用次数
     */
    public static function getUserMonthUsage($userId, $toolId = null)
    {
        $query = self::where('user_id', $userId)
                    ->whereTime('create_time', 'month');
        
        if ($toolId) {
            $query->where('tool_id', $toolId);
        }
        
        return $query->count();
    }
    
    /**
     * 获取学校今日使用次数
     */
    public static function getSchoolTodayUsage($schoolId, $toolId = null)
    {
        $query = self::where('school_id', $schoolId)
                    ->whereTime('create_time', 'today');
        
        if ($toolId) {
            $query->where('tool_id', $toolId);
        }
        
        return $query->count();
    }
    
    /**
     * 获取学校本月使用次数
     */
    public static function getSchoolMonthUsage($schoolId, $toolId = null)
    {
        $query = self::where('school_id', $schoolId)
                    ->whereTime('create_time', 'month');
        
        if ($toolId) {
            $query->where('tool_id', $toolId);
        }
        
        return $query->count();
    }
    
    /**
     * 获取使用统计
     */
    public static function getUsageStatistics($schoolId = null, $toolId = null, $dateRange = 'month')
    {
        $query = self::where('status', self::STATUS_SUCCESS);
        
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        if ($toolId) {
            $query->where('tool_id', $toolId);
        }
        
        if ($dateRange) {
            $query->whereTime('create_time', $dateRange);
        }
        
        $totalUsage = $query->count();
        $totalTokens = $query->sum('tokens_used');
        $totalCost = $query->sum('cost');
        
        return [
            'total_usage' => $totalUsage,
            'total_tokens' => $totalTokens,
            'total_cost' => $totalCost,
            'avg_tokens_per_usage' => $totalUsage > 0 ? round($totalTokens / $totalUsage, 2) : 0,
            'avg_cost_per_usage' => $totalUsage > 0 ? round($totalCost / $totalUsage, 4) : 0
        ];
    }
    
    /**
     * 记录使用情况
     */
    public static function recordUsage($data)
    {
        $usage = new self();
        $usage->tool_id = $data['tool_id'];
        $usage->user_id = $data['user_id'];
        $usage->school_id = $data['school_id'];
        $usage->request_data = $data['request_data'] ?? null;
        $usage->response_data = $data['response_data'] ?? null;
        $usage->tokens_used = $data['tokens_used'] ?? 0;
        $usage->cost = $data['cost'] ?? 0;
        $usage->status = $data['status'] ?? self::STATUS_SUCCESS;
        $usage->error_message = $data['error_message'] ?? null;
        
        return $usage->save();
    }
} 