<?php
declare(strict_types=1);

namespace app\service;

use app\model\ContentLibrary;
use app\model\ContentSpace;
use app\model\ContentAudit;
use think\facade\Db;

class ContentService
{
    /**
     * 创建个人空间内容（上传或AI生成）
     */
    public static function createPersonalContent(array $data, int $userId, int $schoolId)
    {
        Db::startTrans();
        try {
            // 1. 创建内容记录
            $content = new ContentLibrary();
            $content->name        = $data['name'];
            $content->content     = $data['content'] ?? '';
            $content->file_type   = $data['file_type'] ?? ContentLibrary::FILE_TYPE_TEXT;
            $content->source_type = $data['source_type'] ?? ContentLibrary::SOURCE_TYPE_UPLOAD;
            $content->ai_tool_code = $data['ai_tool_code'] ?? '';
            $content->creator_id  = $userId;
            $content->school_id   = $schoolId;
            $content->status      = ContentLibrary::STATUS_DRAFT;
            $content->file_path   = $data['file_path'] ?? '';
            $content->file_size   = $data['file_size'] ?? 0;
            $content->save();

            // 2. 写入个人空间
            ContentSpace::addToSpace(
                $content->id,
                'personal', // space_type
                null,       // space_id
                $userId,
                'private',  // visibility
                'edit'      // permission_level
            );

            Db::commit();
            return $content;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 提交审核
     */
    public static function submitForAudit(int $contentId, int $userId, int $courseId = null, string $visibility = 'public')
    {
        $content = ContentLibrary::find($contentId);
        if (!$content || $content->creator_id !== $userId) {
            throw new \Exception('内容不存在或无权限');
        }
        if ($content->status !== ContentLibrary::STATUS_DRAFT) {
            throw new \Exception('当前状态不可提交');
        }

        $content->status = ContentLibrary::STATUS_PENDING;
        $content->save();

        // 写审核记录
        $audit = new ContentAudit();
        $audit->school_id        = $content->school_id;
        $audit->file_id          = $content->id;
        $audit->content_type     = ContentAudit::TYPE_FILE;
        $audit->content_title    = $content->name;
        $audit->submitter_id     = $userId;
        $audit->submitter_type   = 'teacher';
        if ($courseId) {
            $audit->course_id = $courseId;
        }
        $audit->status           = ContentAudit::STATUS_PENDING;
        $audit->save();

        return true;
    }

    /**
     * 修改可见性 leader/public
     */
    public static function changeVisibility(int $contentId, int $userId, string $visibility)
    {
        if (!in_array($visibility, ['public', 'leader'])) {
            throw new \Exception('可见性取值错误');
        }

        // 只能修改课程空间的空间记录
        $space = ContentSpace::where('content_id', $contentId)
            ->where('space_type', 'course')
            ->where('is_active', 1)
            ->find();
        if (!$space) {
            throw new \Exception('课程空间记录不存在');
        }
        // 判断是否拥有权限（创建者或负责人）
        $content = ContentLibrary::find($contentId);
        if (!$content) {
            throw new \Exception('内容不存在');
        }
        if ($content->creator_id != $userId) {
            // 不是创建者，判断是否负责人
            $leaderId = Db::name('course')->where('id',$space->space_id)->value('responsible_teacher_id');
            if ($leaderId != $userId) {
                // 判断是否课程成员
                $teacherId = Db::name('teacher')->where('user_id',$userId)->value('id');
                $isMember = false;
                if ($teacherId) {
                    $isMember = Db::name('course_teacher')
                        ->where('course_id', $space->space_id)
                        ->where('teacher_id', $teacherId)
                        ->count() > 0;
                }
                if (!$isMember) {
                    throw new \Exception('无权限修改可见性');
                }
            }
        }

        $space->visibility = $visibility;
        $space->save();
        return true;
    }

    /**
     * 个人空间列表
     */
    public static function personalList(int $userId, int $page = 1, int $limit = 15)
    {
        $query = ContentLibrary::where('creator_id', $userId)
            ->where('is_deleted', 0)
            ->order('create_time', 'desc');
        $total = $query->count();
        $list  = $query->page($page, $limit)->select();
        
        // 为每个内容添加关联的文件信息
        foreach ($list as &$content) {
            // 查询关联的文件
            $relatedFiles = \app\model\File::where('content_id', $content->id)
                ->where('status', \app\model\File::STATUS_NORMAL)
                ->field('id, file_name, original_name, file_path, file_size, file_type, mime_type, source_type, ai_tool_code')
                ->select();
            
            $content->related_files = $relatedFiles ? $relatedFiles->toArray() : [];
        }
        
        return compact('list', 'total', 'page', 'limit');
    }

    /**
     * 课程空间列表（过滤权限）
     */
    public static function courseList(int $userId, int $page = 1, int $limit = 15)
    {
        // 教师ID（非 userId）
        $teacherId = Db::name('teacher')->where('user_id',$userId)->value('id');
        if(!$teacherId){
            return ['list'=>[], 'total'=>0, 'page'=>$page, 'limit'=>$limit];
        }

        $subQueryCourseMember = Db::name('course_teacher')
            ->where('teacher_id', $teacherId)
            ->field('course_id');

        $query = ContentLibrary::alias('cl')
            ->leftJoin('content_space cs', "cs.content_id = cl.id AND cs.space_type = 'course' AND cs.is_active = 1")
            ->field('cl.*, cs.visibility')
            ->where('cl.is_deleted', 0)
            ->whereExists(function($sp) use ($teacherId, $subQueryCourseMember){
                $sp->name('content_space')->alias('cs')
                   ->whereRaw('cs.content_id = cl.id')
                   ->where('cs.space_type', 'course')
                   ->where('cs.is_active', 1)
                   ->where(function($vis) use ($teacherId, $subQueryCourseMember){
                        $vis->where('cs.visibility', 'public')
                            ->whereRaw('cs.space_id IN ('.$subQueryCourseMember->buildSql().')')
                            ->whereOr(function($lead) use ($teacherId, $subQueryCourseMember){
                                $lead->where('cs.visibility', 'leader')
                                     ->whereExists(function($c) use ($teacherId, $subQueryCourseMember){
                                         $c->name('course')->alias('co')
                                           ->whereRaw('co.id = cs.space_id')
                                           ->where('co.responsible_teacher_id', $teacherId);
                                     });
                            });
                   });
            });
        $total = $query->count();
        $list = $query->order('create_time', 'desc')->page($page, $limit)->select();
        
        // 为每个内容添加关联的文件信息
        foreach ($list as &$content) {
            // 查询关联的文件
            $relatedFiles = \app\model\File::where('content_id', $content->id)
                ->where('status', \app\model\File::STATUS_NORMAL)
                ->field('id, file_name, original_name, file_path, file_size, file_type, mime_type, source_type, ai_tool_code')
                ->select();
            
            $content->related_files = $relatedFiles ? $relatedFiles->toArray() : [];
        }
        
        return compact('list','total','page','limit');
    }

    /**
     * 审核通过后迁移内容到课程空间
     */
    public static function moveToCourse(int $contentId, int $courseId, string $visibility = 'public')
    {
        if (!in_array($visibility, ['public', 'leader'])) {
            $visibility = 'public';
        }

        // 1. 失效个人空间
        ContentSpace::where([
            ['content_id', '=', $contentId],
            ['space_type', '=', 'personal'],
            ['is_active', '=', 1]
        ])->update(['is_active' => 0]);

        // 2. 激活或插入课程空间
        $space = ContentSpace::where('content_id', $contentId)
            ->where('space_type', 'course')
            ->where('space_id', $courseId)
            ->find();

        if ($space) {
            $space->is_active = 1;
            $space->visibility = $visibility;
            $space->save();
        } else {
            ContentSpace::create([
                'content_id' => $contentId,
                'space_type' => 'course',
                'space_id'   => $courseId,
                'visibility' => $visibility,
                'permission_level' => 'view',
                'is_active'  => 1,
                'create_time'=> date('Y-m-d H:i:s')
            ]);
        }

        // 3. 确保教师与课程关联（方便后续权限）
        $creatorUserId = ContentLibrary::where('id', $contentId)->value('creator_id');
        $teacherId = Db::name('teacher')->where('user_id', $creatorUserId)->value('id');
        if ($teacherId) {
            try {
                Db::name('course_teacher')->insert([
                    'course_id' => $courseId,
                    'teacher_id'=> $teacherId,
                    'role'      => 'teacher',
                    'create_time'=> date('Y-m-d H:i:s')
                ], false, true); // 第二个参数 false 不replace，第三个 true 过滤重复主键
            } catch (\Exception $e) {
                // 如果仍然因为唯一键报错，则忽略
            }
        }
    }
} 