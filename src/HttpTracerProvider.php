<?php

namespace Qscmf\HttpTracer;

use Bootstrap\Provider;
use Bootstrap\LaravelProvider;
use Bootstrap\RegisterContainer;
use Qscmf\HttpTracer\Lib\Helper;

class HttpTracerProvider implements Provider, LaravelProvider {

    public function register(){
    }

    public function registerLara()
    {

        if(Helper::getTableName()){
            RegisterContainer::registerMigration(__DIR__.'/migrations');
        }
        
    }

}