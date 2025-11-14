<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiRequestsLog extends Migration
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
        Schema::create(\Qscmf\HttpTracer\Lib\MigrationCommon::getTableName(false), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('trace_id', 32)->comment("@title=追踪ID;@require=true;");
            $table->string('method', 10)->comment("@title=请求方法;@require=true;");
            $table->text('url')->comment("@title=请求URL;@require=true;");
            $table->text('request_headers')->comment("@title=请求头;@require=true;");
            $table->text('request_body')->default(null)->nullable(true)->comment("@title=请求体;");
            $table->integer('response_status_code')->default(null)->nullable(true)->comment("@title=响应状态码;");
            $table->text('response_headers')->default(null)->nullable(true)->comment("@title=响应头;");
            $table->text('response_body')->default(null)->nullable(true)->comment("@title=响应体;");
            $table->float('duration_ms')->default(null)->nullable(true)->comment("@title=请求耗时(毫秒);");
            $table->timestamp('update_date', 4)->default(DB::raw('CURRENT_TIMESTAMP(4) ON UPDATE CURRENT_TIMESTAMP(4)'));
            $table->timestamp('create_date', 4)->default(DB::raw('CURRENT_TIMESTAMP(4)'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(\Qscmf\HttpTracer\Lib\MigrationCommon::getTableName());
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
