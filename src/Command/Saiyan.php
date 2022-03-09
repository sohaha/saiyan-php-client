<?php

namespace Zls\Saiyan\Command;

use Z;
use Zls\Command\Command;

class Saiyan extends Command
{
    public function execute($args)
    {
        try {
            $active = Z::arrayGet($args, 2, 'help');
            if (in_array($active, get_class_methods('Zls\Saiyan\Operation'), true)) {
                $tip = \Zls\Saiyan\Operation::$active($args);
                if (!$tip) {
                    $this->printStrN($tip);
                }
            } else {
                $this->help($args);
            }
        } catch (\Zls_Exception_Exit $e) {
            $this->printStrN($e->getMessage());
        }
    }

    public function options()
    {
        return [
            '--port' => '8181',
        ];
    }

    public function example()
    {
        return [];
    }

    public function description()
    {
        return 'Saiyan Serve';
    }

    public function commands()
    {
        return [
            ' restart' => ['Restart saiyan service'],
            ' start' => ['Start saiyan service'],
            ' download' => ['Download the saiyan startup file'],
        ];
    }
}
