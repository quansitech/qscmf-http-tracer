<?php 

namespace Qscmf\HttpTracer\Lib;

class Helper{

    public static function getTableName():string
    {

        $table = env('QSCMF_HTTP_TRACE_LOGGER_TABLE_NAME', '');
        if (empty($table)) {
           throw new \Exception("请在.env文件中设置 QSCMF_HTTP_TRACE_LOGGER_TABLE_NAME ");
        }

        return $table;

    }

    public static function getLogFilePath() : string {
        return __DIR__ . '/../logs/qscmf_http_tracer.log';
    }


}