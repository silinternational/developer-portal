<?php

namespace Sil\DevPortal\components\log;

use CLogRoute;

class StreamLogRoute extends CLogRoute
{
    protected function processLogs($logs)
    {
        $stdout = fopen('php://stdout', 'w');
        if ($stdout === false) {
            return;
        }
        foreach ($logs as $log) {
            fwrite($stdout, $log[0] . "\n"); //write the message [1] = level, [2]=category
        }
        fclose($stdout);
    }
}
