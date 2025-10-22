<?php

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\service\VisitorTrackingService;

class CleanVisitorRecords extends Command
{
    protected function configure()
    {
        $this->setName('clean:visitor-records')
            ->setDescription('清理过期的访问记录（保留30天）');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $deletedCount = VisitorTrackingService::cleanExpiredRecords();
            $output->writeln("成功清理了 {$deletedCount} 条过期访问记录");
            return 0;
        } catch (\Exception $e) {
            $output->writeln("清理访问记录失败：" . $e->getMessage());
            return 1;
        }
    }
}
