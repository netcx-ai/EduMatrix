<?php
declare (strict_types = 1);

namespace app\controller\common;

use app\controller\api\BaseController;
use app\service\SmsService;
use think\App;
use think\Request;
use think\Response;

class Sms extends BaseController
{
    protected $smsService;

    public function __construct(App $app, SmsService $smsService)
    {
        parent::__construct($app);
        $this->smsService = $smsService;
    }

    /**
     * 发送验证码
     * @param Request $request
     * @return Response
     */
    public function sendCode(Request $request): Response
    {
        $phone = $request->post('phone');
        
        // 验证手机号格式
        if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
            return json(['code' => 400, 'message' => '手机号格式不正确']);
        }
        
        try {
            $result = $this->smsService->sendCode($phone);
            // 直接返回 SmsService 的结果
            return json($result);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 验证验证码
     * @param Request $request
     * @return Response
     */
    public function verifyCode(Request $request): Response
    {
        $phone = $request->post('phone');
        $code = $request->post('code');
        
        if (empty($phone) || empty($code)) {
            return json(['code' => 400, 'message' => '参数不完整']);
        }
        
        $result = $this->smsService->verifyCode($phone, $code);
        if ($result) {
            return json(['code' => 200, 'message' => '验证码验证成功']);
        }
        return json(['code' => 400, 'message' => '验证码错误或已过期']);
    }
} 