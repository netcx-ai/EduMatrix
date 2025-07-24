<?php
declare(strict_types=1);

namespace app\service;

use app\model\ContentLibrary;
use app\model\File;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use think\file\UploadedFile;
use app\service\StorageService;

/**
 * 文档导出服务
 */
class DocumentExportService
{
    /**
     * 导出内容为Word文档
     * @param int $contentId 内容ID
     * @param string $format 格式：docx, pdf
     * @return array 返回文件信息
     */
    public static function exportToWord(int $contentId, string $format = 'docx'): array
    {
        // 获取内容信息
        $content = ContentLibrary::with(['creator', 'school'])->find($contentId);
        if (!$content) {
            throw new \Exception('内容不存在');
        }

        // 创建PHPWord实例
        $phpWord = new PhpWord();
        
        // 设置文档属性
        $properties = $phpWord->getDocInfo();
        $properties->setCreator($content->creator->name ?? 'EduMatrix');
        $properties->setTitle($content->name);
        $properties->setDescription('AI生成的教学内容');
        $properties->setSubject('教学文档');
        $properties->setKeywords('教育, AI, 教学');
        $properties->setCategory('教学文档');
        $properties->setCreated(time());

        // 添加样式
        $phpWord->addTitleStyle(1, ['bold' => true, 'size' => 18, 'color' => '2E86AB']);
        $phpWord->addTitleStyle(2, ['bold' => true, 'size' => 16, 'color' => '2E86AB']);
        $phpWord->addTitleStyle(3, ['bold' => true, 'size' => 14, 'color' => '2E86AB']);
        
        // 创建段落样式
        $paragraphStyle = [
            'spacing' => 120,
            'spaceBefore' => 120,
            'spaceAfter' => 120,
            'lineHeight' => 1.5,
        ];
        
        $phpWord->addParagraphStyle('Normal', $paragraphStyle);
        
        // 创建强调样式
        $phpWord->addFontStyle('Emphasis', ['bold' => true, 'color' => 'D62839']);
        $phpWord->addFontStyle('Code', ['name' => 'Courier New', 'size' => 10, 'bgColor' => 'F8F9FA']);

        // 创建节
        $section = $phpWord->addSection();

        // 添加标题
        $section->addTitle($content->name, 1);
        $section->addTextBreak(1);

        // 添加文档信息表格
        $section->addText('文档信息', null, ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);
        
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => 'CCCCCC']);
        $table->addRow();
        $table->addCell(2000)->addText('创建者', null, ['bold' => true]);
        $table->addCell(4000)->addText($content->creator->name ?? '未知');
        $table->addRow();
        $table->addCell(2000)->addText('创建时间', null, ['bold' => true]);
        $table->addCell(4000)->addText($content->create_time);
        $table->addRow();
        $table->addCell(2000)->addText('来源', null, ['bold' => true]);
        $table->addCell(4000)->addText($content->source_type == 'ai_generate' ? 'AI生成' : '文件上传');
        if ($content->ai_tool_code) {
            $table->addRow();
            $table->addCell(2000)->addText('AI工具', null, ['bold' => true]);
            $table->addCell(4000)->addText($content->ai_tool_code);
        }
        $section->addTextBreak(2);

        // 添加内容
        $section->addText('文档内容', null, ['bold' => true, 'size' => 14]);
        $section->addTextBreak(1);

        // 处理内容格式
        $formattedContent = self::formatContent($content->content);
        $section->addText($formattedContent, null, $paragraphStyle);

        // 生成文件名
        $fileName = self::generateFileName($content->name, $format);
        
        // 先保存到临时目录
        $tempPath = self::getTempPath($fileName);
        $writerType = $format == 'docx' ? 'Word2007' : 'PDF';
        $objWriter = IOFactory::createWriter($phpWord, $writerType);
        $objWriter->save($tempPath);

        // 获取文件大小（在删除临时文件前）
        $fileSize = filesize($tempPath);

        // 封装成 UploadedFile 对象
        $mimeType = $format == 'docx' ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'application/pdf';
        $uploadedFile = new UploadedFile($tempPath, $fileName, $mimeType, UPLOAD_ERR_OK, true);
        
        // 自动上传到本地/OSS/COS
        $savePath = 'documents/' . date('Y/m');
        $relativePath = StorageService::uploadFile($uploadedFile, $savePath);

        // 清理临时文件
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // 更新下载统计
        if ($content->statistics) {
            $content->statistics->incrementDownloadCount();
        }

        return [
            'file_path' => $relativePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'mime_type' => $format == 'docx' ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'application/pdf'
        ];
    }

    /**
     * 获取临时文件路径
     */
    private static function getTempPath(string $fileName): string
    {
        $tempDir = runtime_path() . 'temp' . DIRECTORY_SEPARATOR;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        return $tempDir . $fileName;
    }

    /**
     * 格式化内容，处理Markdown语法
     */
    private static function formatContent(?string $content): string
    {
        if (empty($content)) {
            return '内容为空';
        }
        
        // 处理标题
        $content = preg_replace('/^### (.*$)/m', '$1', $content); // 三级标题
        $content = preg_replace('/^## (.*$)/m', '$1', $content);  // 二级标题
        $content = preg_replace('/^# (.*$)/m', '$1', $content);   // 一级标题
        
        // 处理粗体
        $content = preg_replace('/\*\*(.*?)\*\*/', '$1', $content);
        
        // 处理斜体
        $content = preg_replace('/\*(.*?)\*/', '$1', $content);
        
        // 处理代码块
        $content = preg_replace('/```(.*?)```/s', '$1', $content);
        
        // 处理行内代码
        $content = preg_replace('/`(.*?)`/', '$1', $content);
        
        // 处理列表
        $content = preg_replace('/^\* (.*$)/m', '• $1', $content);
        $content = preg_replace('/^- (.*$)/m', '• $1', $content);
        
        // 处理数字列表
        $content = preg_replace('/^\d+\. (.*$)/m', '$1', $content);
        
        // 处理换行
        $content = str_replace('\n\n', "\n\n", $content);
        
        return $content;
    }

    /**
     * 生成文件名
     */
    private static function generateFileName(string $contentName, string $format): string
    {
        $cleanName = preg_replace('/[^\w\s-]/', '', $contentName);
        $cleanName = preg_replace('/\s+/', '_', $cleanName);
        return $cleanName . '_' . date('YmdHis') . '.' . $format;
    }

    /**
     * 清理过期文件
     */
    public static function cleanupExpiredFiles(int $maxAge = 86400): void
    {
        $storageType = \app\helper\SystemHelper::getStorageDriver();
        
        switch (strtolower($storageType)) {
            case 'local':
                self::cleanupLocalFiles($maxAge);
                break;
            case 'oss':
                self::cleanupOSSFiles($maxAge);
                break;
            case 'cos':
                self::cleanupCOSFiles($maxAge);
                break;
        }
    }

    /**
     * 清理本地过期文件
     */
    private static function cleanupLocalFiles(int $maxAge): void
    {
        $uploadDir = root_path() . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR;
        if (!is_dir($uploadDir)) {
            return;
        }

        $files = glob($uploadDir . '*');
        $now = time();

        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAge) {
                unlink($file);
            }
        }
    }

    /**
     * 清理OSS过期文件
     */
    private static function cleanupOSSFiles(int $maxAge): void
    {
        // TODO: 实现OSS文件清理
    }

    /**
     * 清理COS过期文件
     */
    private static function cleanupCOSFiles(int $maxAge): void
    {
        // TODO: 实现COS文件清理
    }
} 