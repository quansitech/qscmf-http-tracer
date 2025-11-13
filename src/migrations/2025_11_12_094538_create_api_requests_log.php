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
            $table->string('trace_id', 32);
            $table->string('method', 10);
            $table->text('url');
            $table->text('request_headers');
            $table->text('request_body')->default(null)->nullable(true);
            $table->integer('response_status_code')->default(null)->nullable(true);
            $table->text('response_headers')->default(null)->nullable(true);
            $table->text('response_body')->default(null)->nullable(true);
            $table->float('duration_ms')->default(null)->nullable(true);
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
