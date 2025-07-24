<?php
namespace app\model;

use think\Model;

/**
 * 内容操作日志模型
 */
class ContentLog extends Model
{
    protected $name = 'content_log';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'content_id' => 'integer',
        'user_id' => 'integer',
        'school_id' => 'integer',
        'action_type' => 'integer',
        'create_time' => 'datetime'
    ];
    
    // 操作类型常量
    const ACTION_CREATE = 1;      // 创建
    const ACTION_UPDATE = 2;      // 更新
    const ACTION_DELETE = 3;      // 删除
    const ACTION_VIEW = 4;        // 查看
    const ACTION_DOWNLOAD = 5;    // 下载
    const ACTION_SHARE = 6;       // 分享
    const ACTION_MOVE = 7;        // 移动
    const ACTION_COPY = 8;        // 复制
    const ACTION_AUDIT = 9;       // 审核
    const ACTION_APPROVE = 10;    // 审批通过
    const ACTION_REJECT = 11;     // 审批驳回
    
    /**
     * 获取操作类型列表
     */
    public static function getActionTypeList()
    {
        return [
            self::ACTION_CREATE => '创建',
            self::ACTION_UPDATE => '更新',
            self::ACTION_DELETE => '删除',
            self::ACTION_VIEW => '查看',
            self::ACTION_DOWNLOAD => '下载',
            self::ACTION_SHARE => '分享',
            self::ACTION_MOVE => '移动',
            self::ACTION_COPY => '复制',
            self::ACTION_AUDIT => '审核',
            self::ACTION_APPROVE => '审批通过',
            self::ACTION_REJECT => '审批驳回'
        ];
    }
    
    /**
     * 获取操作类型名称
     */
    public function getActionTypeTextAttr($value, $data)
    {
        $types = self::getActionTypeList();
        return isset($types[$data['action_type']]) ? $types[$data['action_type']] : '';
    }
    
    /**
     * 关联内容
     */
    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id', 'id');
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
     * 记录操作日志
     */
    public static function recordLog($contentId, $userId, $schoolId, $actionType, $actionDetail = '', $ip = '', $userAgent = '')
    {
        $log = new self();
        $log->content_id = $contentId;
        $log->user_id = $userId;
        $log->school_id = $schoolId;
        $log->action_type = $actionType;
        $log->action_detail = $actionDetail;
        $log->ip = $ip ?: request()->ip();
        $log->user_agent = $userAgent ?: request()->header('User-Agent');
        $log->save();
        
        return $log;
    }
    
    /**
     * 获取内容操作日志
     */
    public static function getContentLogs($contentId, $page = 1, $limit = 20)
    {
        return self::with(['user'])
                  ->where('content_id', $contentId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 获取用户操作日志
     */
    public static function getUserLogs($userId, $schoolId, $page = 1, $limit = 20)
    {
        return self::with(['content'])
                  ->where('user_id', $userId)
                  ->where('school_id', $schoolId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 获取学校操作日志
     */
    public static function getSchoolLogs($schoolId, $page = 1, $limit = 20)
    {
        return self::with(['user', 'content'])
                  ->where('school_id', $schoolId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 获取操作统计
     */
    public static function getActionStatistics($schoolId, $startDate = null, $endDate = null)
    {
        $query = self::where('school_id', $schoolId);
        
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        return $query->group('action_type')
                    ->field('action_type, COUNT(*) as count')
                    ->select();
    }
    
    /**
     * 获取热门内容（按查看次数）
     */
    public static function getHotContents($schoolId, $limit = 10)
    {
        $contentIds = self::where('school_id', $schoolId)
                         ->where('action_type', self::ACTION_VIEW)
                         ->group('content_id')
                         ->field('content_id, COUNT(*) as view_count')
                         ->order('view_count desc')
                         ->limit($limit)
                         ->select();
        
        $contentIds = array_column($contentIds, 'content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 获取用户活跃度统计
     */
    public static function getUserActivityStatistics($schoolId, $startDate = null, $endDate = null)
    {
        $query = self::where('school_id', $schoolId);
        
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        return $query->group('user_id')
                    ->field('user_id, COUNT(*) as activity_count')
                    ->order('activity_count desc')
                    ->select();
    }
    
    /**
     * 清理过期日志
     */
    public static function cleanExpiredLogs($days = 90)
    {
        $expireDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return self::where('create_time', '<', $expireDate)->delete();
    }
    
    /**
     * 获取内容访问统计
     */
    public static function getContentAccessStatistics($contentId, $startDate = null, $endDate = null)
    {
        $query = self::where('content_id', $contentId);
        
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        $statistics = $query->group('action_type')
                           ->field('action_type, COUNT(*) as count')
                           ->select();
        
        $result = [];
        foreach ($statistics as $stat) {
            $result[$stat['action_type']] = $stat['count'];
        }
        
        return $result;
    }
    
    /**
     * 获取操作日志摘要
     */
    public static function getLogSummary($schoolId, $days = 7)
    {
        $startDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $totalLogs = self::where('school_id', $schoolId)
                        ->where('create_time', '>=', $startDate)
                        ->count();
        
        $uniqueUsers = self::where('school_id', $schoolId)
                          ->where('create_time', '>=', $startDate)
                          ->group('user_id')
                          ->count();
        
        $uniqueContents = self::where('school_id', $schoolId)
                             ->where('create_time', '>=', $startDate)
                             ->group('content_id')
                             ->count();
        
        return [
            'total_logs' => $totalLogs,
            'unique_users' => $uniqueUsers,
            'unique_contents' => $uniqueContents,
            'period_days' => $days
        ];
    }
} 