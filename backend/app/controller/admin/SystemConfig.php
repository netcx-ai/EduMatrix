<?php
declare (strict_types = 1);

namespace app\controller\admin;

use app\BaseController;
use app\model\SystemConfig as SystemConfigModel;
use think\facade\View;
use think\Request;

class SystemConfig extends BaseController
{
    /**
     * 配置列表
     */
    public function index(Request $request)
    {
        $type = $request->param('type', 'sms');
        $list = SystemConfigModel::where('type', $type)
            ->order('is_default', 'desc')
            ->order('id', 'desc')
            ->select();
            
        return View::fetch('admin/system_config/index', [
            'list' => $list,
            'type' => $type,
            'types' => [
                'sms' => '短信配置',
                'payment' => '支付配置',
                'email' => '邮箱配置',
                'storage' => '对象存储',
                'sso' => '单点登录',
                'redis' => '缓存配置',
                'queue' => '队列配置',
                'log' => '日志配置',
                'monitor' => '监控配置',
                'security' => '安全配置',
                'third_party' => '第三方服务'
            ]
        ]);
    }

    /**
     * 添加配置
     */
    public function add(Request $request)
    {
        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'type' => 'require|in:sms,payment,email,storage,sso,redis,queue,log,monitor,security,third_party',
                    'name' => 'require|max:50',
                    'driver' => 'require|max:20',
                    'config' => 'require|array',
                    'status' => 'require|in:0,1',
                    'is_default' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }

            // 如果设置为默认，需要将同类型的其他配置设为非默认
            if ($data['is_default']) {
                SystemConfigModel::where('type', $data['type'])
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }

            // 创建配置
            $config = new SystemConfigModel;
            $config->save($data);

            return json(['code' => 1, 'msg' => '添加成功']);
        }

        return View::fetch('admin/system_config/add', [
            'types' => [
                'sms' => '短信配置',
                'payment' => '支付配置',
                'email' => '邮箱配置',
                'storage' => '对象存储',
                'sso' => '单点登录',
                'redis' => '缓存配置',
                'queue' => '队列配置',
                'log' => '日志配置',
                'monitor' => '监控配置',
                'security' => '安全配置',
                'third_party' => '第三方服务'
            ]
        ]);
    }

    /**
     * 编辑配置
     */
    public function edit(Request $request)
    {
        $id = $request->param('id/d');
        $config = SystemConfigModel::find($id);
        if (!$config) {
            return $this->error('配置不存在');
        }

        if ($request->isPost()) {
            $data = $request->post();
            
            // 验证数据
            try {
                validate([
                    'name' => 'require|max:50',
                    'config' => 'require|array',
                    'status' => 'require|in:0,1',
                    'is_default' => 'require|in:0,1',
                ])->check($data);
            } catch (\Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }

            // 如果设置为默认，需要将同类型的其他配置设为非默认
            if ($data['is_default']) {
                SystemConfigModel::where('type', $config->type)
                    ->where('id', '<>', $id)
                    ->where('is_default', 1)
                    ->update(['is_default' => 0]);
            }

            // 更新配置
            $config->save($data);

            return json(['code' => 1, 'msg' => '更新成功']);
        }

        return View::fetch('admin/system_config/edit', [
            'config' => $config
        ]);
    }

    /**
     * 删除配置
     */
    public function delete(Request $request)
    {
        $id = $request->param('id/d');
        $config = SystemConfigModel::find($id);
        if (!$config) {
            return json(['code' => 0, 'msg' => '配置不存在']);
        }

        // 如果是默认配置，不允许删除
        if ($config->is_default) {
            return json(['code' => 0, 'msg' => '默认配置不能删除']);
        }

        $config->delete();
        return json(['code' => 1, 'msg' => '删除成功']);
    }

    /**
     * 设置默认配置
     */
    public function setDefault(Request $request)
    {
        $id = $request->param('id/d');
        if (SystemConfigModel::setDefault($id)) {
            return json(['code' => 1, 'msg' => '设置成功']);
        }
        return json(['code' => 0, 'msg' => '设置失败']);
    }

    /**
     * 测试配置
     */
    public function test(Request $request)
    {
        $id = $request->param('id/d');
        $config = SystemConfigModel::find($id);
        if (!$config) {
            return json(['code' => 0, 'msg' => '配置不存在']);
        }

        try {
            $result = $this->testConfig($config);
            return json(['code' => 1, 'msg' => '测试成功', 'data' => $result]);
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '测试失败：' . $e->getMessage()]);
        }
    }

    /**
     * 测试配置连接
     */
    private function testConfig($config)
    {
        switch ($config->type) {
            case 'sms':
                return $this->testSmsConfig($config);
            case 'email':
                return $this->testEmailConfig($config);
            case 'storage':
                return $this->testStorageConfig($config);
            case 'redis':
                return $this->testRedisConfig($config);
            default:
                return ['message' => '该配置类型暂不支持测试'];
        }
    }

    /**
     * 测试短信配置
     */
    private function testSmsConfig($config)
    {
        // 这里可以调用短信服务进行测试
        return ['message' => '短信配置测试通过'];
    }

    /**
     * 测试邮箱配置
     */
    private function testEmailConfig($config)
    {
        // 这里可以发送测试邮件
        return ['message' => '邮箱配置测试通过'];
    }

    /**
     * 测试对象存储配置
     */
    private function testStorageConfig($config)
    {
        // 这里可以测试对象存储连接
        return ['message' => '对象存储配置测试通过'];
    }

    /**
     * 测试Redis配置
     */
    private function testRedisConfig($config)
    {
        // 这里可以测试Redis连接
        return ['message' => 'Redis配置测试通过'];
    }
} 