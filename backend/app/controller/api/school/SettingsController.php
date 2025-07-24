<?php
declare (strict_types = 1);

namespace app\controller\api\school;

use app\BaseController;
use app\model\School;
use think\facade\Validate;
use think\facade\Log;

class SettingsController extends BaseController
{
    /**
     * 获取学校设置
     */
    public function index()
    {
        $user = $this->request->user;
        
        try {
            $school = School::find($user->primary_school_id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            // 返回学校基本信息作为设置
            $settings = [
                'schoolName' => $school->name ?? '',
                'schoolCode' => $school->code ?? '',  // 添加学校编码
                'schoolAddress' => $school->address ?? '',
                'phone' => $school->phone ?? '',
                'description' => $school->description ?? ''
            ];
            
            return json(['code' => 200, 'data' => $settings]);
            
        } catch (\Exception $e) {
            Log::error("获取学校设置失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '获取学校设置失败']);
        }
    }
    
    /**
     * 保存学校设置
     */
    public function store()
    {
        $user = $this->request->user;
        $data = $this->request->post();
        
        // 验证参数
        $validate = Validate::rule([
            'schoolName' => 'require|length:2,100',
            'schoolCode' => 'require|length:2,50|alphaNum',  // 添加学校编码验证规则
            'schoolAddress' => 'length:0,255',
            'phone' => 'length:0,20',
            'description' => 'length:0,500'
        ])->message([
            'schoolName.require' => '学校名称不能为空',
            'schoolName.length' => '学校名称长度必须在2-100个字符之间',
            'schoolCode.require' => '学校编码不能为空',
            'schoolCode.length' => '学校编码长度必须在2-50个字符之间',
            'schoolCode.alphaNum' => '学校编码只能包含字母和数字',
            'schoolAddress.length' => '学校地址长度不能超过255个字符',
            'phone.length' => '联系电话长度不能超过20个字符',
            'description.length' => '学校描述长度不能超过500个字符'
        ]);
        
        if (!$validate->check($data)) {
            return json(['code' => 400, 'message' => $validate->getError()]);
        }
        
        try {
            $school = School::find($user->primary_school_id);
            
            if (!$school) {
                return json(['code' => 404, 'message' => '学校不存在']);
            }
            
            // 更新学校信息
            $school->name = $data['schoolName'];
            $school->code = $data['schoolCode'];
            $school->address = $data['schoolAddress'] ?? '';
            $school->phone = $data['phone'] ?? '';
            $school->description = $data['description'] ?? '';
            
            $school->save();
            
            return json(['code' => 200, 'message' => '设置保存成功']);
            
        } catch (\Exception $e) {
            Log::error("保存学校设置失败: " . $e->getMessage());
            return json(['code' => 500, 'message' => '保存学校设置失败']);
        }
    }
}
