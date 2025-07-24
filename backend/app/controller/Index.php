<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;

class Index
{
    public function hello($name = 'ThinkPHP6')
    {
        return json([
            'code' => 200,
            'message' => 'Hello ' . $name,
            'data' => [
                'time' => date('Y-m-d H:i:s')
            ]
        ]);
    }
} 