<?php
namespace app\model;

use think\Model;

/**
 * 内容统计模型
 */
class ContentStatistics extends Model
{
    protected $name = 'content_statistics';
    protected $pk = 'id';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 字段类型转换
    protected $type = [
        'id' => 'integer',
        'content_id' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'share_count' => 'integer',
        'like_count' => 'integer',
        'comment_count' => 'integer',
        'last_view_time' => 'datetime',
        'last_download_time' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    /**
     * 关联内容
     */
    public function content()
    {
        return $this->belongsTo(ContentLibrary::class, 'content_id', 'id');
    }
    
    /**
     * 增加查看次数
     */
    public function incrementViewCount()
    {
        $this->view_count++;
        $this->last_view_time = date('Y-m-d H:i:s');
        $this->save();
        
        return $this;
    }
    
    /**
     * 增加下载次数
     */
    public function incrementDownloadCount()
    {
        $this->download_count++;
        $this->last_download_time = date('Y-m-d H:i:s');
        $this->save();
        
        return $this;
    }
    
    /**
     * 增加分享次数
     */
    public function incrementShareCount()
    {
        $this->share_count++;
        $this->save();
        
        return $this;
    }
    
    /**
     * 增加点赞次数
     */
    public function incrementLikeCount()
    {
        $this->like_count++;
        $this->save();
        
        return $this;
    }
    
    /**
     * 减少点赞次数
     */
    public function decrementLikeCount()
    {
        if ($this->like_count > 0) {
            $this->like_count--;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * 增加评论次数
     */
    public function incrementCommentCount()
    {
        $this->comment_count++;
        $this->save();
        
        return $this;
    }
    
    /**
     * 减少评论次数
     */
    public function decrementCommentCount()
    {
        if ($this->comment_count > 0) {
            $this->comment_count--;
            $this->save();
        }
        
        return $this;
    }
    
    /**
     * 获取热门内容（按查看次数）
     */
    public static function getHotContentsByViews($schoolId, $limit = 10)
    {
        $contentIds = self::alias('s')
                         ->join('edu_content_library c', 's.content_id = c.id')
                         ->where('c.school_id', $schoolId)
                         ->where('c.is_deleted', 0)
                         ->order('s.view_count desc')
                         ->limit($limit)
                         ->column('s.content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 获取热门内容（按下载次数）
     */
    public static function getHotContentsByDownloads($schoolId, $limit = 10)
    {
        $contentIds = self::alias('s')
                         ->join('edu_content_library c', 's.content_id = c.id')
                         ->where('c.school_id', $schoolId)
                         ->where('c.is_deleted', 0)
                         ->order('s.download_count desc')
                         ->limit($limit)
                         ->column('s.content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 获取热门内容（按点赞次数）
     */
    public static function getHotContentsByLikes($schoolId, $limit = 10)
    {
        $contentIds = self::alias('s')
                         ->join('edu_content_library c', 's.content_id = c.id')
                         ->where('c.school_id', $schoolId)
                         ->where('c.is_deleted', 0)
                         ->order('s.like_count desc')
                         ->limit($limit)
                         ->column('s.content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 获取内容统计信息
     */
    public static function getContentStatistics($schoolId, $startDate = null, $endDate = null)
    {
        $query = self::alias('s')
                    ->join('edu_content_library c', 's.content_id = c.id')
                    ->where('c.school_id', $schoolId)
                    ->where('c.is_deleted', 0);
        
        if ($startDate) {
            $query->where('c.create_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('c.create_time', '<=', $endDate);
        }
        
        $totalViews = $query->sum('s.view_count');
        $totalDownloads = $query->sum('s.download_count');
        $totalShares = $query->sum('s.share_count');
        $totalLikes = $query->sum('s.like_count');
        $totalComments = $query->sum('s.comment_count');
        
        return [
            'total_views' => $totalViews,
            'total_downloads' => $totalDownloads,
            'total_shares' => $totalShares,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments
        ];
    }
    
    /**
     * 获取用户内容统计
     */
    public static function getUserContentStatistics($userId, $schoolId)
    {
        $query = self::alias('s')
                    ->join('edu_content_library c', 's.content_id = c.id')
                    ->where('c.creator_id', $userId)
                    ->where('c.school_id', $schoolId)
                    ->where('c.is_deleted', 0);
        
        $totalViews = $query->sum('s.view_count');
        $totalDownloads = $query->sum('s.download_count');
        $totalShares = $query->sum('s.share_count');
        $totalLikes = $query->sum('s.like_count');
        $totalComments = $query->sum('s.comment_count');
        $contentCount = $query->count('DISTINCT s.content_id');
        
        return [
            'content_count' => $contentCount,
            'total_views' => $totalViews,
            'total_downloads' => $totalDownloads,
            'total_shares' => $totalShares,
            'total_likes' => $totalLikes,
            'total_comments' => $totalComments
        ];
    }
    
    /**
     * 获取每日统计趋势
     */
    public static function getDailyStatistics($schoolId, $days = 7)
    {
        $startDate = date('Y-m-d', strtotime("-{$days} days"));
        
        $statistics = [];
        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $nextDate = date('Y-m-d', strtotime("-{$i} days +1 day"));
            
            $views = self::alias('s')
                        ->join('edu_content_library c', 's.content_id = c.id')
                        ->where('c.school_id', $schoolId)
                        ->where('c.is_deleted', 0)
                        ->where('s.last_view_time', '>=', $date)
                        ->where('s.last_view_time', '<', $nextDate)
                        ->count();
            
            $downloads = self::alias('s')
                           ->join('edu_content_library c', 's.content_id = c.id')
                           ->where('c.school_id', $schoolId)
                           ->where('c.is_deleted', 0)
                           ->where('s.last_download_time', '>=', $date)
                           ->where('s.last_download_time', '<', $nextDate)
                           ->count();
            
            $statistics[] = [
                'date' => $date,
                'views' => $views,
                'downloads' => $downloads
            ];
        }
        
        return array_reverse($statistics);
    }
    
    /**
     * 获取内容热度评分
     */
    public function getHeatScore()
    {
        // 热度评分算法：查看次数*1 + 下载次数*3 + 分享次数*2 + 点赞次数*5 + 评论次数*4
        $score = $this->view_count * 1 + 
                $this->download_count * 3 + 
                $this->share_count * 2 + 
                $this->like_count * 5 + 
                $this->comment_count * 4;
        
        return $score;
    }
    
    /**
     * 获取热门内容（按热度评分）
     */
    public static function getHotContentsByHeatScore($schoolId, $limit = 10)
    {
        $contents = self::alias('s')
                       ->join('edu_content_library c', 's.content_id = c.id')
                       ->where('c.school_id', $schoolId)
                       ->where('c.is_deleted', 0)
                       ->field('s.content_id, (s.view_count*1 + s.download_count*3 + s.share_count*2 + s.like_count*5 + s.comment_count*4) as heat_score')
                       ->order('heat_score desc')
                       ->limit($limit)
                       ->select();
        
        $contentIds = array_column($contents, 'content_id');
        
        return ContentLibrary::whereIn('id', $contentIds)
                           ->order('FIELD(id, ' . implode(',', $contentIds) . ')')
                           ->select();
    }
    
    /**
     * 重置内容统计
     */
    public function resetStatistics()
    {
        $this->view_count = 0;
        $this->download_count = 0;
        $this->share_count = 0;
        $this->like_count = 0;
        $this->comment_count = 0;
        $this->last_view_time = null;
        $this->last_download_time = null;
        $this->save();
        
        return $this;
    }
    
    /**
     * 批量更新统计信息
     */
    public static function batchUpdateStatistics($contentId, $data)
    {
        $statistics = self::where('content_id', $contentId)->find();
        
        if (!$statistics) {
            $statistics = new self();
            $statistics->content_id = $contentId;
        }
        
        foreach ($data as $field => $value) {
            if (property_exists($statistics, $field)) {
                $statistics->$field = $value;
            }
        }
        
        $statistics->save();
        
        return $statistics;
    }
} 