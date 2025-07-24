<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MigrateFilePaths extends Migrator
{
    public function up()
    {
        // 创建uploads目录（如果不存在）
        $uploadsPath = app()->getRootPath() . 'public/uploads';
        if (!is_dir($uploadsPath)) {
            mkdir($uploadsPath, 0755, true);
        }
        
        // 迁移现有文件
        $storagePath = app()->getRootPath() . 'public/storage';
        if (is_dir($storagePath)) {
            // 复制storage目录下的所有文件到uploads目录
            $this->copyDirectory($storagePath, $uploadsPath);
            
            // 更新数据库中的文件路径（移除storage前缀）
            $this->execute("UPDATE edu_file SET file_path = REPLACE(file_path, 'storage/', '') WHERE file_path LIKE 'storage/%'");
        }
    }
    
    public function down()
    {
        // 回滚：恢复storage前缀
        $this->execute("UPDATE edu_file SET file_path = CONCAT('storage/', file_path) WHERE file_path NOT LIKE 'storage/%'");
    }
    
    /**
     * 复制目录
     */
    private function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourcePath = $source . '/' . $file;
                $destPath = $destination . '/' . $file;
                
                if (is_dir($sourcePath)) {
                    $this->copyDirectory($sourcePath, $destPath);
                } else {
                    copy($sourcePath, $destPath);
                }
            }
        }
        closedir($dir);
    }
} 