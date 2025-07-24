<?php
declare(strict_types=1);

namespace app\controller\admin;

use app\BaseController;
use app\model\UserLog;
use think\Request;
use think\Response;
use think\facade\View;

class UserLogController extends BaseController
{
    /**
     * 用户操作日志列表
     */
    public function index(Request $request)
    {
        try {
            // 如果是AJAX请求（layui table默认是GET），返回JSON
            if ($request->isAjax()) {
                $page = (int)$request->param('page', 1);
                $limit = (int)$request->param('limit', 15);
                $username = $request->param('username', '');
                $status = $request->param('status', '');
                $type = $request->param('type', '');
                $dateRange = $request->param('date_range', '');
                
                $query = UserLog::with(['user']);
                
                // 只显示用户操作记录，不显示管理员操作
                $query->where('login_type', '!=', 'admin');
                
                // 按用户名搜索
                if ($username) {
                    $query->hasWhere('user', function($query) use ($username) {
                        $query->where('username|real_name', 'like', "%{$username}%");
                    });
                }
                
                // 按状态筛选
                if ($status !== '') {
                    $query->where('login_status', intval($status));
                }
                
                // 按操作类型筛选
                if ($type) {
                    $query->where('login_type', $type);
                }
                
                // 按日期范围筛选
                if ($dateRange) {
                    $dates = explode(' - ', $dateRange);
                    if (count($dates) == 2) {
                        $query->whereBetween('login_time', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                    }
                }
                
                $total = $query->count();
                $logs = $query->order('login_time', 'desc')
                             ->page($page, $limit)
                             ->select();
                
                $data = [];
                foreach ($logs as $log) {
                    $data[] = [
                        'id' => $log->id,
                        'user_id' => $log->user_id,
                        'username' => $log->user ? $log->user->username : '-',
                        'real_name' => $log->user ? $log->user->real_name : '-',
                        'login_time' => $log->login_time,
                        'login_ip' => $log->login_ip,
                        'login_device' => $log->login_device,
                        'login_status' => $log->login_status,
                        'login_type' => $log->login_type,
                        'type_text' => $log->getUserTypeText(),
                        'fail_reason' => $log->fail_reason
                    ];
                }
                
                return json([
                    'code' => 0,
                    'msg' => '',
                    'count' => $total,
                    'data' => $data
                ]);
            }
            
            // 非AJAX请求，返回页面
            return \think\facade\View::fetch('admin/user_log/index');
            
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('UserLog index error: ' . $e->getMessage());
            \think\facade\Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->isAjax()) {
                return json([
                    'code' => 1,
                    'msg' => '请求异常：' . $e->getMessage(),
                    'count' => 0,
                    'data' => []
                ]);
            } else {
                return $this->error('页面加载失败：' . $e->getMessage());
            }
        }
    }
    
    /**
     * 清理指定天数前的日志
     */
    public function clean(Request $request)
    {
        try {
            $days = $request->param('days', 30);
            $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            $count = UserLog::where('login_time', '<', $date)->delete();
            
            return json([
                'code' => 0,
                'msg' => "成功清理 {$count} 条日志",
                'data' => null
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 1,
                'msg' => '清理失败：' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
    
    /**
     * 导出日志
     */
    public function export(Request $request)
    {
        try {
            $username = $request->param('username', '');
            $status = $request->param('status', '');
            $type = $request->param('type', '');
            $dateRange = $request->param('date_range', '');
            
            $query = UserLog::with(['user']);
            
            // 只显示用户操作记录，不显示管理员操作
            $query->where('login_type', '!=', 'admin');
            
            // 按用户名搜索
            if ($username) {
                $query->hasWhere('user', function($query) use ($username) {
                    $query->where('username|real_name', 'like', "%{$username}%");
                });
            }
            
            // 按状态筛选
            if ($status !== '') {
                $query->where('login_status', intval($status));
            }
            
            // 按操作类型筛选
            if ($type) {
                $query->where('login_type', $type);
            }
            
            // 按日期范围筛选
            if ($dateRange) {
                $dates = explode(' - ', $dateRange);
                if (count($dates) == 2) {
                    $query->whereBetween('login_time', [$dates[0] . ' 00:00:00', $dates[1] . ' 23:59:59']);
                }
            }
            
            $logs = $query->order('login_time', 'desc')->select();
            
            // 设置响应头，直接下载CSV文件
            $filename = 'user_logs_' . date('Y-m-d_H-i-s') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            
            // 输出BOM，解决中文乱码问题
            echo "\xEF\xBB\xBF";
            
            // 创建输出流
            $output = fopen('php://output', 'w');
            
            // 写入CSV头部
            fputcsv($output, ['用户ID', '用户名', '姓名', '操作时间', '操作IP', '操作设备', '状态', '操作类型', '失败原因']);
            
            // 写入数据
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log->user_id,
                    $log->user ? $log->user->username : '-',
                    $log->user ? $log->user->real_name : '-',
                    $log->login_time,
                    $log->login_ip,
                    $log->login_device,
                    $log->login_status ? '成功' : '失败',
                    $log->getUserTypeText(),
                    $log->fail_reason ?: '-'
                ]);
            }
            
            fclose($output);
            exit;

        } catch (\Exception $e) {
            \think\facade\Log::error('Export user log error: ' . $e->getMessage());
            return json(['code' => 1, 'msg' => '导出失败：' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取操作类型列表
     */
    public function getTypeList()
    {
        return json([
            'code' => 0,
            'msg' => '',
            'data' => UserLog::getUserTypeList()
        ]);
    }
} 