<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\SystemSetting as SystemSettingModel;
use app\model\SystemConfig;
use think\facade\View;
use think\Request;

class SystemSetting extends BaseController
{
    /**
     * 系统设置首页
     */
    public function index(Request $request)
    {
        try {
            $group = $request->param('group', 'basic');
            
            // 清除缓存以确保获取最新数据
            SystemSettingModel::clearCache();
            
            $settings = SystemSettingModel::getGroupSettings($group);
            

            
            return View::fetch('admin/system_setting/index', [
                'settings' => $settings,
                'group' => $group,
                'groups' => SystemSettingModel::getGroupOptions()
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log("SystemSetting index error: " . $e->getMessage());
            
            // 返回错误页面或JSON响应
            if ($request->isAjax()) {
                return json(['code' => 0, 'msg' => '页面加载失败：' . $e->getMessage()]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }

    /**
     * 保存设置
     */
    public function save(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            $group = $data['group'] ?? 'basic';
            
            // 移除group字段
            unset($data['group']);
            
            try {
                $result = SystemSettingModel::batchUpdate($data, $group);
                if ($result) {
                    // 如果是上传设置，同步到 SystemConfig 表
                    if ($group === 'upload' && isset($data['storage_driver'])) {
                        $this->syncStorageConfig($data);
                    }
                    return json(['code' => 1, 'msg' => '保存成功']);
                } else {
                    return json(['code' => 0, 'msg' => '保存失败']);
                }
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '保存失败：' . $e->getMessage()]);
            }
        }
        
        return json(['code' => 0, 'msg' => '请求方式错误']);
    }

    /**
     * 同步存储配置到 SystemConfig 表
     * @param array $data 上传设置数据
     */
    private function syncStorageConfig(array $data)
    {
        // 获取存储驱动
        $driver = $data['storage_driver'] ?? 'local';
        
        // 检查是否已存在该驱动的配置
        $config = SystemConfig::where('type', 'storage')
            ->where('driver', $driver)
            ->find();
        
        // 如果不存在，创建新配置
        if (!$config) {
            $config = new SystemConfig();
            $config->type = 'storage';
            $config->name = '存储配置 - ' . $this->getDriverName($driver);
            $config->driver = $driver;
            $config->status = 1;
        }
        
        // 根据驱动类型设置配置
        switch ($driver) {
            case 'oss':
                $configData = [
                    'access_id' => env('OSS.access_id', ''),
                    'access_secret' => env('OSS.access_secret', ''),
                    'bucket' => env('OSS.bucket', ''),
                    'endpoint' => env('OSS.endpoint', ''),
                    'url' => env('OSS.url', ''),
                ];
                break;
            case 'cos':
                $configData = [
                    'secret_id' => env('COS.secret_id', ''),
                    'secret_key' => env('COS.secret_key', ''),
                    'bucket' => env('COS.bucket', ''),
                    'region' => env('COS.region', ''),
                    'url' => env('COS.url', ''),
                ];
                break;
            case 'local':
            default:
                $configData = [
                    'root' => app()->getRootPath() . 'public/uploads',
                    'url' => '/uploads',
                ];
                break;
        }
        
        // 设置配置数据
        $config->config = $configData;
        
        // 如果是默认驱动，设置为默认
        if ($driver === $data['storage_driver']) {
            $config->is_default = 1;
            
            // 将其他存储配置设置为非默认
            SystemConfig::where('type', 'storage')
                ->where('driver', '<>', $driver)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);
        }
        
        // 保存配置
        $config->save();
    }
    
    /**
     * 获取驱动名称
     * @param string $driver 驱动标识
     * @return string 驱动名称
     */
    private function getDriverName(string $driver): string
    {
        $names = [
            'local' => '本地存储',
            'oss' => '阿里云OSS',
            'cos' => '腾讯云COS',
        ];
        
        return $names[$driver] ?? '未知存储';
    }

    /**
     * 添加配置项
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'group' => 'require',
                    'key' => 'require|max:100',
                    'title' => 'require|max:100',
                    'type' => 'require|in:text,textarea,number,select,switch,image,file,color,date,datetime,time,radio,checkbox,editor',
                    'sort' => 'integer',
                    'status' => 'in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }

            // 检查键名是否已存在
            $exists = SystemSettingModel::where('group', $data['group'])
                ->where('key', $data['key'])
                ->find();
            
            if ($exists) {
                return json(['code' => 0, 'msg' => '配置键名已存在']);
            }

            // 创建配置
            $setting = new SystemSettingModel;
            $result = $setting->save($data);

            if ($result) {
                // 清除缓存
                SystemSettingModel::clearCache();
                return json(['code' => 1, 'msg' => '添加成功']);
            } else {
                return json(['code' => 0, 'msg' => '添加失败']);
            }
        }

        return View::fetch('admin/system_setting/add', [
            'groups' => SystemSettingModel::getGroupOptions(),
            'types' => SystemSettingModel::getTypeOptions()
        ]);
    }

    /**
     * 编辑配置项
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        $setting = SystemSettingModel::find($id);
        
        if (!$setting) {
            return $this->error('配置项不存在');
        }

        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'group' => 'require',
                    'key' => 'require|max:100',
                    'title' => 'require|max:100',
                    'type' => 'require|in:text,textarea,number,select,switch,image,file,color,date,datetime,time,radio,checkbox,editor',
                    'sort' => 'integer',
                    'status' => 'in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }

            // 检查键名是否已存在（排除当前记录）
            $exists = SystemSettingModel::where('group', $data['group'])
                ->where('key', $data['key'])
                ->where('id', '<>', $id)
                ->find();
            
            if ($exists) {
                return json(['code' => 0, 'msg' => '配置键名已存在']);
            }

            // 更新配置
            $result = $setting->save($data);

            if ($result) {
                // 清除缓存
                SystemSettingModel::clearCache();
                return json(['code' => 1, 'msg' => '更新成功']);
            } else {
                return json(['code' => 0, 'msg' => '更新失败']);
            }
        }

        return View::fetch('admin/system_setting/edit', [
            'setting' => $setting,
            'groups' => SystemSettingModel::getGroupOptions(),
            'types' => SystemSettingModel::getTypeOptions()
        ]);
    }

    /**
     * 删除配置项
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $setting = SystemSettingModel::find($id);
        
        if (!$setting) {
            return json(['code' => 0, 'msg' => '配置项不存在']);
        }

        $result = $setting->delete();
        
        if ($result) {
            // 清除缓存
            SystemSettingModel::clearCache();
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     * 获取配置值
     */
    public function getValue(Request $request)
    {
        $key = $request->param('key');
        $value = SystemSettingModel::getSettingValue($key);
        
        return json(['code' => 1, 'data' => $value]);
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        SystemSettingModel::clearCache();
        return json(['code' => 1, 'msg' => '缓存清除成功']);
    }

    /**
     * 导出配置
     */
    public function export()
    {
        $settings = SystemSettingModel::getAllSettings();
        
        $filename = 'system_settings_' . date('Y-m-d_H-i-s') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 导入配置
     */
    public function import(Request $request)
    {
        if ($request->isPost()) {
            $file = $request->file('file');
            
            if (!$file) {
                return json(['code' => 0, 'msg' => '请选择文件']);
            }

            try {
                $content = file_get_contents($file->getPathname());
                $data = json_decode($content, true);
                
                if (!$data) {
                    return json(['code' => 0, 'msg' => '文件格式错误']);
                }

                // 批量导入配置
                foreach ($data as $group => $settings) {
                    foreach ($settings as $key => $setting) {
                        $exists = SystemSettingModel::where('group', $group)
                            ->where('key', $key)
                            ->find();
                        
                        if ($exists) {
                            // 更新现有配置
                            $exists->save($setting);
                        } else {
                            // 创建新配置
                            $setting['group'] = $group;
                            $setting['key'] = $key;
                            $newSetting = new SystemSettingModel;
                            $newSetting->save($setting);
                        }
                    }
                }

                // 清除缓存
                SystemSettingModel::clearCache();
                
                return json(['code' => 1, 'msg' => '导入成功']);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => '导入失败：' . $e->getMessage()]);
            }
        }

        return View::fetch('admin/system_setting/import');
    }

    /**
     * 测试方法 - 用于调试
     */
    public function test()
    {
        try {
            $group = 'basic';
            $settings = SystemSettingModel::getGroupSettings($group);
            
            echo "<h2>测试系统设置数据</h2>";
            echo "<p>分组: {$group}</p>";
            echo "<p>配置数量: " . count($settings) . "</p>";
            
            echo "<h3>配置列表:</h3>";
            foreach ($settings as $setting) {
                echo "<div style='border:1px solid #ccc; margin:5px; padding:10px;'>";
                echo "<strong>标题:</strong> " . $setting['title'] . "<br>";
                echo "<strong>键名:</strong> " . $setting['key'] . "<br>";
                echo "<strong>值:</strong> " . $setting['value'] . "<br>";
                echo "<strong>类型:</strong> " . $setting['type'] . "<br>";
                echo "<strong>选项:</strong> " . json_encode($setting['options']) . "<br>";
                echo "</div>";
            }
            
        } catch (\Exception $e) {
            echo "<h2>错误信息</h2>";
            echo "<p>错误: " . $e->getMessage() . "</p>";
            echo "<p>文件: " . $e->getFile() . "</p>";
            echo "<p>行号: " . $e->getLine() . "</p>";
        }
    }
} 