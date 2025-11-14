<?php

namespace Qscmf\HttpTracer\Lib\LogWriter;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Qscmf\HttpTracer\Lib\FileFormatter;
use Qscmf\HttpTracer\Lib\Helper;
use Qscmf\HttpTracer\Lib\LogWriter\DBHandler;
use Qscmf\HttpTracer\Lib\LogWriter\LogWriterInterface;
use Qscmf\HttpTracer\Lib\LogWriter\MonologLogWriter;

class Context{

    public static function buildWriter(string $type):LogWriterInterface{
        switch($type){
            case 'file':
                $fileMonolog = new Logger('qscmf_http_tracer_file');
                $handler = new RotatingFileHandler(Helper::getLogFilePath());
                $handler->setFormatter(new FileFormatter());
                $fileMonolog->pushHandler($handler);
                $logger =  new MonologLogWriter($fileMonolog);

                break;

            case 'db':
                $db_monolog = new Logger('qscmf_http_tracer_db');
                $handler = new DBHandler();
                $db_monolog->pushHandler($handler);
                $logger =  new MonologLogWriter($db_monolog);

                break;
        }


        return $logger;
    }

}