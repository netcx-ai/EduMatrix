<?php
namespace app\controller\api\teacher;

use app\controller\api\BaseController;
use app\model\File;
use app\model\ContentLibrary;
use app\model\AiUsage;
use app\model\Course;
use think\Request;

class StatsController extends BaseController
{
    /**
     * 教师个人统计概览
     */
    public function overview(Request $request)
    {
        $userId = $request->userId;

        // 文件统计
        $totalFiles = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('status', File::STATUS_NORMAL)
            ->count();

        $totalSize = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('status', File::STATUS_NORMAL)
            ->sum('file_size');

        $todayUploads = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->whereDay('create_time', 'today')
            ->count();

        // 各类型文件统计
        $documentCount = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('file_category', File::CATEGORY_DOCUMENT)
            ->where('status', File::STATUS_NORMAL)
            ->count();

        $imageCount = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('file_category', File::CATEGORY_IMAGE)
            ->where('status', File::STATUS_NORMAL)
            ->count();

        $videoCount = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('file_category', File::CATEGORY_VIDEO)
            ->where('status', File::STATUS_NORMAL)
            ->count();

        $audioCount = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('file_category', File::CATEGORY_AUDIO)
            ->where('status', File::STATUS_NORMAL)
            ->count();

        $otherCount = File::where('uploader_id', $userId)
            ->where('uploader_type', 'teacher')
            ->where('file_category', File::CATEGORY_OTHER)
            ->where('status', File::STATUS_NORMAL)
            ->count();

        // 内容统计
        $totalContent = ContentLibrary::where('creator_id', $userId)->where('is_deleted', 0)->count();
        $draftContent = ContentLibrary::where('creator_id', $userId)->where('status', ContentLibrary::STATUS_DRAFT)->where('is_deleted', 0)->count();
        $pendingContent = ContentLibrary::where('creator_id', $userId)->where('status', ContentLibrary::STATUS_PENDING)->where('is_deleted', 0)->count();
        $approvedContent = ContentLibrary::where('creator_id', $userId)->where('status', ContentLibrary::STATUS_APPROVED)->where('is_deleted', 0)->count();

        // AI使用统计
        $aiUsageCount = AiUsage::where('user_id', $userId)
            ->where('status', AiUsage::STATUS_SUCCESS)
            ->count();

        // 课程统计（教师参与的课程，包括负责的课程和参与的课程）
        $courseCount = Course::where('school_id', $request->user->school_id)
            ->where(function($q) use ($userId) {
                $q->where('responsible_teacher_id', $userId)
                  ->whereOr('id', 'in', function($subQuery) use ($userId) {
                      $subQuery->table('edu_course_teacher')
                               ->where('teacher_id', $userId)
                               ->field('course_id');
                  });
            })
            ->where('status', 1) // 只统计启用的课程
            ->count();

        return $this->success([
            'courseCount' => $courseCount,
            'fileCount' => $totalFiles,
            'aiUsageCount' => $aiUsageCount,
            'totalSize' => (int)$totalSize,
            'todayUploads' => $todayUploads,
            'totalContent' => $totalContent,
            'draftContent' => $draftContent,
            'pendingContent' => $pendingContent,
            'approvedContent' => $approvedContent,
            'documentCount' => $documentCount,
            'imageCount' => $imageCount,
            'videoCount' => $videoCount,
            'audioCount' => $audioCount,
            'otherCount' => $otherCount,
        ]);
    }
} 