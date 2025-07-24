<?php

use Phinx\Seed\AbstractSeed;
use think\facade\Db;

class TestDataSeeder extends AbstractSeed
{
    public function run(): void
    {
        // 开启事务确保数据一致性
        Db::startTrans();
        
        try {
            // 1. 学校数据
            $schools = [
                [
                    'name' => '北京大学',
                    'code' => 'PKU',
                    'short_name' => '北大',
                    'description' => '中国著名高等学府',
                    'province' => '北京市',
                    'city' => '北京市',
                    'district' => '海淀区',
                    'school_type' => 1,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ],
                [
                    'name' => '清华大学',
                    'code' => 'THU',
                    'short_name' => '清华',
                    'description' => '中国顶尖理工大学',
                    'province' => '北京市',
                    'city' => '北京市',
                    'district' => '海淀区',
                    'school_type' => 1,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ],
                [
                    'name' => '复旦大学',
                    'code' => 'FDU',
                    'short_name' => '复旦',
                    'description' => '中国著名综合性大学',
                    'province' => '上海市',
                    'city' => '上海市',
                    'district' => '杨浦区',
                    'school_type' => 1,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ],
                [
                    'name' => '浙江大学',
                    'code' => 'ZJU',
                    'short_name' => '浙大',
                    'description' => '中国著名综合性大学',
                    'province' => '浙江省',
                    'city' => '杭州市',
                    'district' => '西湖区',
                    'school_type' => 1,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ],
                [
                    'name' => '南京大学',
                    'code' => 'NJU',
                    'short_name' => '南大',
                    'description' => '中国著名综合性大学',
                    'province' => '江苏省',
                    'city' => '南京市',
                    'district' => '鼓楼区',
                    'school_type' => 1,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ]
            ];
            
            // 清空现有数据
            Db::name('school')->delete(true);
            Db::name('college')->delete(true);
            Db::name('teacher')->delete(true);
            Db::name('course')->delete(true);
            Db::name('user')->where('user_type', 'teacher')->delete(true);
            Db::name('user')->where('user_type', 'school_admin')->delete(true);
            
            $schoolIds = [];
            foreach ($schools as $school) {
                $schoolIds[] = Db::name('school')->insertGetId($school);
            }

            // 2. 学院数据
            $collegeNames = [
                ['计算机学院','数学学院','物理学院','化学学院'],
                ['计算机系','自动化系','电子工程系'],
                ['管理学院','外国语学院','新闻学院'],
                ['信息与电子工程学院','生命科学学院','建筑工程学院'],
                ['地球科学与工程学院','环境学院','历史学院','哲学学院']
            ];
            
            $colleges = [];
            $collegeIds = [];
            $collegeTeacherCounts = []; // 记录每个学院的教师数量
            
            foreach ($schoolIds as $sIdx => $schoolId) {
                // 确保索引不越界
                $collegeIndex = $sIdx % count($collegeNames);
                $schoolIndex = $sIdx % count($schools);
                foreach ($collegeNames[$collegeIndex] as $cIdx => $cName) {
                    $college = [
                        'school_id' => $schoolId,
                        'name' => $cName,
                        'code' => strtoupper(substr($cName,0,4)).$sIdx.$cIdx, // 修复编码重复问题
                        'short_name' => mb_substr($cName,0,2),
                        'description' => $schools[$schoolIndex]['name'].$cName,
                        'status' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ];
                    $colleges[] = $college;
                    $collegeIds[] = Db::name('college')->insertGetId($college);
                }
            }

            // 3. 教师职称ID列表
            $titleIds = Db::name('teacher_titles')->where('status', 1)->column('id');
            if (empty($titleIds)) {
                $titleIds = [1,2,3,4,5];
            }
            
            // 4. 教师数据（每个学院5-8名教师，title分布均匀）
            $teacherIds = [];
            $teacherNo = 10001;
            $teacherStartIndex = 0; // 记录教师ID的起始索引
            
            foreach ($colleges as $idx => $college) {
                $numTeachers = rand(5,8);
                $collegeTeacherCounts[$idx] = $numTeachers; // 记录当前学院教师数量
                
                for ($i = 0; $i < $numTeachers; $i++) {
                    $titleId = $titleIds[$i % count($titleIds)];
                    $currentTeacherNo = $teacherNo + $i;
                    
                    // 创建用户记录
                    $user = [
                        'username' => 'teacher' . $currentTeacherNo,
                        'password' => password_hash('123456', PASSWORD_DEFAULT),
                        'real_name' => '教师' . $currentTeacherNo . chr(65+$i%26),
                        'phone' => '138'.rand(1000,9999).sprintf('%03d', $currentTeacherNo),
                        'email' => 'teacher' . $currentTeacherNo . '@example.com',
                        'user_type' => 'teacher',
                        'primary_school_id' => $college['school_id'],
                        'teacher_no' => 'T' . $currentTeacherNo,
                        'status' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ];
                    $userId = Db::name('user')->insertGetId($user);
                    
                    // 创建教师记录
                    $teacher = [
                        'school_id' => $college['school_id'],
                        'college_id' => $collegeIds[$idx],
                        'user_id' => $userId, // 关联用户ID
                        'teacher_no' => 'T' . $currentTeacherNo,
                        'real_name' => '教师' . $currentTeacherNo . chr(65+$i%26),
                        'phone' => $user['phone'],
                        'email' => $user['email'],
                        'gender' => rand(1,2),
                        'title' => $titleId,
                        'department' => $college['name'],
                        'position' => ['讲师','副教授','教授','助教'][($i)%4],
                        'education' => ['博士','硕士','本科'][rand(0,2)],
                        'major' => ['计算机科学','数学','物理','化学','管理','外语','新闻','生物','建筑','地理','环境','历史','哲学'][rand(0,12)],
                        'hire_date' => date('Y-m-d', strtotime('-' . rand(1,15) . ' year')),
                        'status' => 1,
                        'is_verified' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ];
                    $teacherIds[] = Db::name('teacher')->insertGetId($teacher);
                }
                $teacherNo += $numTeachers;
            }
            
            // 5. 课程数据（每个学院3-5门课程，关联教师）
            $courseNo = 20001;
            $teacherStartIndex = 0; // 重新初始化教师索引
            
            foreach ($colleges as $idx => $college) {
                $numCourses = rand(3,5);
                $currentCollegeTeacherCount = $collegeTeacherCounts[$idx]; // 获取当前学院教师数量
                $collegeTeacherIds = array_slice($teacherIds, $teacherStartIndex, $currentCollegeTeacherCount);
                
                for ($i = 0; $i < $numCourses; $i++) {
                    $tId = $collegeTeacherIds[$i % count($collegeTeacherIds)];
                    $course = [
                        'school_id' => $college['school_id'],
                        'college_id' => $collegeIds[$idx],
                        'course_code' => 'C' . $courseNo++,
                        'name' => $college['name'].'课程'.($i+1),
                        'description' => '测试课程描述'.($i+1),
                        'credits' => rand(2,4),
                        'hours' => rand(32,64),
                        'semester' => ['2024春','2024秋','2025春'][rand(0,2)],
                        'academic_year' => ['2023-2024','2024-2025'][rand(0,1)],
                        'responsible_teacher_id' => $tId,
                        'status' => 1,
                        'is_public' => 1,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ];
                    Db::name('course')->insert($course);
                }
                $teacherStartIndex += $currentCollegeTeacherCount; // 更新教师起始索引
            }
            
            // 6. 为每个学校创建学校管理员
            foreach ($schoolIds as $sIdx => $schoolId) {
                $adminNo = $schoolId;
                $schoolIndex = $sIdx % count($schools); // 确保索引安全
                
                // 创建学校管理员用户
                $adminUser = [
                    'username' => 'schooladmin' . $adminNo,
                    'password' => password_hash('123456', PASSWORD_DEFAULT),
                    'real_name' => $schools[$schoolIndex]['name'] . '管理员',
                    'phone' => '13900000' . sprintf('%03d', $adminNo),
                    'email' => 'admin@' . strtolower($schools[$schoolIndex]['code']) . '.edu.cn',
                    'user_type' => 'school_admin',
                    'primary_school_id' => $schoolId,
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ];
                $adminUserId = Db::name('user')->insertGetId($adminUser);
                
                // 创建学校管理员记录
                $schoolAdmin = [
                    'school_id' => $schoolId,
                    'user_id' => $adminUserId,
                    'username' => 'schooladmin' . $adminNo,
                    'password' => $adminUser['password'],
                    'real_name' => $schools[$schoolIndex]['name'] . '管理员',
                    'phone' => $adminUser['phone'],
                    'email' => $adminUser['email'],
                    'role' => 'admin',
                    'department' => '信息中心',
                    'position' => '系统管理员',
                    'status' => 1,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ];
                Db::name('school_admin')->insert($schoolAdmin);
            }
            
            // 7. AI工具权限（为每个学校分配所有AI工具权限，每日200次、每月5000次）
            $aiTools = Db::name('ai_tool')->where('status', 1)->column('id');
            if (!empty($aiTools)) {
                foreach ($schoolIds as $schoolId) {
                    foreach ($aiTools as $toolId) {
                        $exist = Db::name('ai_tool_school')->where('school_id', $schoolId)->where('tool_id', $toolId)->find();
                        if ($exist) {
                            Db::name('ai_tool_school')->where('id', $exist['id'])->update([
                                'status' => 1,
                                'daily_limit' => 200,
                                'monthly_limit' => 5000,
                                'update_time' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            Db::name('ai_tool_school')->insert([
                                'school_id' => $schoolId,
                                'tool_id' => $toolId,
                                'status' => 1,
                                'daily_limit' => 200,
                                'monthly_limit' => 5000,
                                'create_time' => date('Y-m-d H:i:s'),
                                'update_time' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
            }
            
            // 提交事务
            Db::commit();
            
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            throw $e;
        }
    }
} 