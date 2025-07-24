<?php
use think\migration\Migrator;

class RemoveFileIdFromContentLibrary extends Migrator
{
    public function change()
    {
        $table = $this->table('content_library');
        if ($table->hasColumn('file_id')) {
            $table->removeColumn('file_id');
            $table->update();
        }
    }
} 