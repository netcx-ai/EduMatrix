<?php
namespace app\model;

use think\Model;

/**
 * 内容分享模型
 */
class ContentShare extends Model
{
    protected $name = 'content_share';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'content_id' => 'integer',
        'user_id' => 'integer',
        'school_id' => 'integer',
        'share_type' => 'integer',
        'access_count' => 'integer',
        'is_active' => 'boolean',
        'expire_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 分享类型常量
    const SHARE_TYPE_PUBLIC = 1;      // 公开分享
    const SHARE_TYPE_PRIVATE = 2;     // 私密分享
    const SHARE_TYPE_PASSWORD = 3;    // 密码分享
    const SHARE_TYPE_LINK = 4;        // 链接分享
    
    /**
     * 获取分享类型列表
     */
    public static function getShareTypeList()
    {
        return [
            self::SHARE_TYPE_PUBLIC => '公开分享',
            self::SHARE_TYPE_PRIVATE => '私密分享',
            self::SHARE_TYPE_PASSWORD => '密码分享',
            self::SHARE_TYPE_LINK => '链接分享'
        ];
    }
    
    /**
     * 获取分享类型名称
     */
    public function getShareTypeTextAttr($value, $data)
    {
        $types = self::getShareTypeList();
        return isset($types[$data['share_type']]) ? $types[$data['share_type']] : '';
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
     * 创建分享
     */
    public static function createShare($contentId, $userId, $schoolId, $shareType = self::SHARE_TYPE_PUBLIC, $password = '', $expireTime = null)
    {
        // 检查是否已存在
        $existing = self::where('content_id', $contentId)
                       ->where('user_id', $userId)
                       ->where('share_type', $shareType)
                       ->find();
        
        if ($existing) {
            return $existing;
        }
        
        $share = new self();
        $share->content_id = $contentId;
        $share->user_id = $userId;
        $share->school_id = $schoolId;
        $share->share_type = $shareType;
        $share->share_code = self::generateShareCode();
        $share->password = $password;
        $share->expire_time = $expireTime;
        $share->access_count = 0;
        $share->is_active = 1;
        $share->save();
        
        return $share;
    }
    
    /**
     * 生成分享码
     */
    private static function generateShareCode($length = 8)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        // 检查是否已存在
        while (self::where('share_code', $code)->find()) {
            $code = self::generateShareCode($length);
        }
        
        return $code;
    }
    
    /**
     * 通过分享码获取分享信息
     */
    public static function getByShareCode($shareCode)
    {
        return self::with(['content', 'user'])
                  ->where('share_code', $shareCode)
                  ->where('is_active', 1)
                  ->find();
    }
    
    /**
     * 验证分享访问权限
     */
    public function checkAccess($password = '')
    {
        // 检查是否过期
        if ($this->expire_time && $this->expire_time < date('Y-m-d H:i:s')) {
            return ['valid' => false, 'message' => '分享已过期'];
        }
        
        // 检查分享类型
        switch ($this->share_type) {
            case self::SHARE_TYPE_PUBLIC:
                return ['valid' => true, 'message' => '访问成功'];
                
            case self::SHARE_TYPE_PRIVATE:
                return ['valid' => false, 'message' => '私密分享，需要特定权限'];
                
            case self::SHARE_TYPE_PASSWORD:
                if (empty($password)) {
                    return ['valid' => false, 'message' => '需要访问密码'];
                }
                if ($this->password !== $password) {
                    return ['valid' => false, 'message' => '密码错误'];
                }
                return ['valid' => true, 'message' => '访问成功'];
                
            case self::SHARE_TYPE_LINK:
                return ['valid' => true, 'message' => '访问成功'];
                
            default:
                return ['valid' => false, 'message' => '无效的分享类型'];
        }
    }
    
    /**
     * 增加访问次数
     */
    public function incrementAccessCount()
    {
        $this->access_count++;
        $this->save();
        
        return $this;
    }
    
    /**
     * 获取用户的分享列表
     */
    public static function getUserShares($userId, $schoolId, $page = 1, $limit = 20)
    {
        return self::with(['content'])
                  ->where('user_id', $userId)
                  ->where('school_id', $schoolId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 获取内容的分享列表
     */
    public static function getContentShares($contentId, $page = 1, $limit = 20)
    {
        return self::with(['user'])
                  ->where('content_id', $contentId)
                  ->page($page, $limit)
                  ->order('create_time desc')
                  ->select();
    }
    
    /**
     * 更新分享设置
     */
    public function updateShareSettings($shareType = null, $password = null, $expireTime = null, $isActive = null)
    {
        if ($shareType !== null) {
            $this->share_type = $shareType;
        }
        
        if ($password !== null) {
            $this->password = $password;
        }
        
        if ($expireTime !== null) {
            $this->expire_time = $expireTime;
        }
        
        if ($isActive !== null) {
            $this->is_active = $isActive;
        }
        
        $this->save();
        
        return $this;
    }
    
    /**
     * 取消分享
     */
    public function cancelShare()
    {
        $this->is_active = 0;
        $this->save();
        
        return $this;
    }
    
    /**
     * 获取分享统计信息
     */
    public static function getShareStatistics($schoolId, $startDate = null, $endDate = null)
    {
        $query = self::where('school_id', $schoolId);
        
        if ($startDate) {
            $query->where('create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('create_time', '<=', $endDate);
        }
        
        $totalShares = $query->count();
        $activeShares = $query->where('is_active', 1)->count();
        $totalAccess = $query->sum('access_count');
        
        // 按分享类型统计
        $typeStats = $query->group('share_type')
                          ->field('share_type, COUNT(*) as count')
                          ->select();
        
        return [
            'total_shares' => $totalShares,
            'active_shares' => $activeShares,
            'total_access' => $totalAccess,
            'type_statistics' => $typeStats
        ];
    }
    
    /**
     * 获取热门分享内容
     */
    public static function getHotSharedContents($schoolId, $limit = 10)
    {
        $contentIds = self::where('school_id', $schoolId)
                         ->where('is_active', 1)
                         ->group('content_id')
                         ->field('content_id, SUM(access_count) as total_access')
                         ->order('total_access desc')
                         ->limit($limit)
                         ->select();
        
        $contentIds = array_column($contentIds, 'content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 清理过期分享
     */
    public static function cleanExpiredShares()
    {
        return self::where('expire_time', '<', date('Y-m-d H:i:s'))
                  ->where('is_active', 1)
                  ->update(['is_active' => 0]);
    }
    
    /**
     * 批量取消分享
     */
    public static function batchCancelShares($shareIds)
    {
        return self::whereIn('id', $shareIds)
                  ->update(['is_active' => 0]);
    }
    
    /**
     * 获取分享链接
     */
    public function getShareUrl()
    {
        $baseUrl = request()->domain();
        return $baseUrl . '/share/' . $this->share_code;
    }
    
    /**
     * 获取分享二维码数据
     */
    public function getShareQrCode()
    {
        $shareUrl = $this->getShareUrl();
        // 这里可以集成二维码生成库
        // 暂时返回URL，前端可以调用二维码生成服务
        return $shareUrl;
    }
} 