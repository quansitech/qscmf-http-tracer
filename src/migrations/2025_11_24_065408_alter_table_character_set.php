<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterTableCharacterSet extends Migration
{

    public function beforeCmmUp()
    {
        //
    }

    public function beforeCmmDown()
    {
        //
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table_name = \Qscmf\HttpTracer\Lib\MigrationCommon::getTableName();
        DB::statement("ALTER TABLE `{$table_name}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 字符集修改通常不需要回滚，因为这是修复操作
        // 如果需要回滚，可以恢复到之前的字符集设置
    }

    public function afterCmmUp()
    {
        //
    }

    public function afterCmmDown()
    {
        //
    }
}
