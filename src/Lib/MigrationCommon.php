<?php

namespace Qscmf\HttpTracer\Lib;

use google\rpc\Help;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MigrationCommon
{

    public static function getTableName(bool $need_exists = true):string
    {

        $table = Helper::getTableName();

        if ($need_exists && !Schema::hasTable($table)){
            self::throwErr("表{$table}不存在");
        }

        return $table;

    }

    public static function throwErr(string $msg): void
    {
        throw new \Exception($msg);

    }

    public static function isProduction():bool
    {
        return env('APP_ENV') === 'production';
    }

}
