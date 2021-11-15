<?php

namespace Zls\Saiyan;

use Cfg;
use Exception;
use Z;
use Zls;

class Operation
{
    public function pidPath()
    {
        $tempDirPath = Z::config()->getStorageDirPath();
        return Z::realPathMkdir($tempDirPath . 'saiyan', true, false, false, false) . 'master.pid';
    }

    public function restart()
    {
        $pid = file_get_contents($this->pidPath());
        if ($pid) {
            $cmd = 'kill ' . $pid;
            if (Z::isWin()) {
                $cmd = 'taskkill /f /pid ' . $pid;
            }
            Z::command($cmd, '', false);
        }
    }

    public function writePid($pid)
    {
        return @file_put_contents($this->pidPath(), $pid);
    }
}
