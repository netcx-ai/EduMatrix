<?php
namespace app\model;

use think\Model;

/**
 * AI工具学校权限模型
 */
class AiToolSchool extends Model
{
    protected $name = 'ai_tool_school';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'tool_id' => 'integer',
        'school_id' => 'integer',
        'daily_limit' => 'integer',
        'monthly_limit' => 'integer',
        'status' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
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
     * 关联学校
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id', 'id');
    }
    
    /**
     * 检查学校是否有权限使用指定工具
     */
    public static function checkPermission($schoolId, $toolId)
    {
        $permission = self::where('school_id', $schoolId)
                         ->where('tool_id', $toolId)
                         ->where('status', self::STATUS_ENABLED)
                         ->find();
        
        return $permission ? true : false;
    }
    
    /**
     * 检查学校使用限制
     */
    public static function checkUsageLimit($schoolId, $toolId)
    {
        $permission = self::where('school_id', $schoolId)
                         ->where('tool_id', $toolId)
                         ->where('status', self::STATUS_ENABLED)
                         ->find();
        
        if (!$permission) {
            return ['allowed' => false, 'message' => '该工具未授权给学校使用'];
        }
        
        // 检查每日限制
        if ($permission->daily_limit > 0) {
            $todayUsage = AiUsage::getSchoolTodayUsage($schoolId, $toolId);
            if ($todayUsage >= $permission->daily_limit) {
                return ['allowed' => false, 'message' => '已达到每日使用限制'];
            }
        }
        
        // 检查每月限制
        if ($permission->monthly_limit > 0) {
            $monthUsage = AiUsage::getSchoolMonthUsage($schoolId, $toolId);
            if ($monthUsage >= $permission->monthly_limit) {
                return ['allowed' => false, 'message' => '已达到每月使用限制'];
            }
        }
        
        return ['allowed' => true, 'permission' => $permission];
    }
    
    /**
     * 获取学校可用工具列表
     */
    public static function getSchoolAvailableTools($schoolId)
    {
        return self::with(['tool'])
                  ->where('school_id', $schoolId)
                  ->where('status', self::STATUS_ENABLED)
                  ->whereHas('tool', function($query) {
                      $query->where('status', AiTool::STATUS_ENABLED);
                  })
                  ->select();
    }
    
    /**
     * 获取工具授权学校列表
     */
    public static function getToolAuthorizedSchools($toolId)
    {
        return self::with(['school'])
                  ->where('tool_id', $toolId)
                  ->where('status', self::STATUS_ENABLED)
                  ->select();
    }
    
    /**
     * 批量授权工具给学校
     */
    public static function batchAuthorize($toolIds, $schoolIds, $limits = [])
    {
        $data = [];
        foreach ($toolIds as $toolId) {
            foreach ($schoolIds as $schoolId) {
                $data[] = [
                    'tool_id' => $toolId,
                    'school_id' => $schoolId,
                    'daily_limit' => $limits['daily_limit'] ?? 100,
                    'monthly_limit' => $limits['monthly_limit'] ?? 3000,
                    'status' => self::STATUS_ENABLED,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ];
            }
        }
        
        return self::insertAll($data);
    }
    
    /**
     * 批量取消授权
     */
    public static function batchRevoke($toolIds, $schoolIds)
    {
        return self::whereIn('tool_id', $toolIds)
                  ->whereIn('school_id', $schoolIds)
                  ->update(['status' => self::STATUS_DISABLED]);
    }
    
    /**
     * 获取学校工具使用统计
     */
    public function getUsageStatistics()
    {
        $todayUsage = AiUsage::getSchoolTodayUsage($this->school_id, $this->tool_id);
        $monthUsage = AiUsage::getSchoolMonthUsage($this->school_id, $this->tool_id);
        
        return [
            'daily_limit' => $this->daily_limit,
            'monthly_limit' => $this->monthly_limit,
            'today_usage' => $todayUsage,
            'month_usage' => $monthUsage,
            'daily_remaining' => $this->daily_limit > 0 ? max(0, $this->daily_limit - $todayUsage) : -1,
            'monthly_remaining' => $this->monthly_limit > 0 ? max(0, $this->monthly_limit - $monthUsage) : -1
        ];
    }
} 