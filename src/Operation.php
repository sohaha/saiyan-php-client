<?php

namespace Zls\Saiyan;

use Exception;
use Z;
use Zls;
use Zls\Saiyan\Command\Bin;
use Zls_Task;

class Operation
{
    public static function start($args)
    {
        $port = (int)Z::getOpt("port", 8181);
        if ($port <= 1) {
            $port = 8181;
        }
        $isSaiyan = Z::server('SAIYAN_VERSION');
        if ($isSaiyan) {
            self::run($args);
            return;
        }
        $bin = (new Bin())->path();
        Z::command("{$bin} -D --port {$port}");
    }

    public static function run($args)
    {
        $relay = new Relay(STDIN, STDOUT);
        $flags = 0;
        $getConfig = Zls::getConfig();
        while (true) {
            $args = $relay->receive($flags);
            if (is_null($args)) {
                continue;
            }
            if ($args === false) {
                return "illegal execution, process termination";
            }
            try {
                $type = Z::arrayGet($args, 'type');
                $content = "";
                switch ($type) {
                    case 'task':
                        $activity = str_replace('/', '_', Z::arrayGet($args, 'task'));
                        if ($activity) {
                            $taskName = $getConfig->getTaskDirName() . '_' . $activity;
                            $taskObject = Z::factory($taskName, true);
                            Z::throwIf(!($taskObject instanceof Zls_Task), 500, '[ ' . $taskName . ' ] not a valid Zls_Task', 'ERROR');
                            ob_start();
                            $taskObject->_execute($args);
                            $content = ob_get_clean();
                        }
                    default:
                }
                $relay->send($content, Parse::PAYLOAD_RAW);
            } catch (Exception $e) {
                $relay->send($e->getMessage(), Parse::PAYLOAD_CONTROL & Parse::PAYLOAD_ERROR);
            }
        }
    }

    public static function restart()
    {
        self::command('restart');
    }

    private static function command($direct)
    {
        $bin = (new Bin())->path();
        Z::command("{$bin} {$direct}");
    }

    public static function stop()
    {
        self::command('stop');
    }

    public static function download()
    {
        (new Bin())->download();
    }
}
