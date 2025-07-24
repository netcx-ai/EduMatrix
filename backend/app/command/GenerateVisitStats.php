<?php
declare (strict_types = 1);

namespace app\command;

use app\model\VisitStats;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class GenerateVisitStats extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('visit:stats')
            ->setDescription('生成访问统计数据');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('开始生成访问统计数据...');
        
        try {
            // 生成昨天的统计数据
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $result = VisitStats::generateStats($yesterday);
            
            if ($result) {
                $output->writeln("成功生成 {$yesterday} 的访问统计数据");
            } else {
                $output->writeln("生成 {$yesterday} 的访问统计数据失败");
            }
            
            // 可以选择生成多天的数据
            $days = 7; // 生成最近7天的数据
            for ($i = 2; $i <= $days; $i++) {
                $date = date('Y-m-d', strtotime("-{$i} day"));
                $result = VisitStats::generateStats($date);
                
                if ($result) {
                    $output->writeln("成功生成 {$date} 的访问统计数据");
                } else {
                    $output->writeln("生成 {$date} 的访问统计数据失败");
                }
            }
            
            $output->writeln('访问统计数据生成完成');
            
        } catch (\Exception $e) {
            $output->writeln('生成访问统计数据时发生错误: ' . $e->getMessage());
        }
    }
} 