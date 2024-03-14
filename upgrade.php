<?php

use App\Helpers\Classes\InstallationHelper;
use App\Models\OpenAIGenerator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function beforeUpdate(): bool
{
    return true;
}

function afterUpdate(): bool
{

    /*
    Yeni gelen tabloları migrate ediyoruz.
    --force sebebi ise environmentin productionda olduğunda are you sure? diye bir uyarı veriyor bunu atlamak
    */
    Artisan::call('migrate', [
        '--force' => true,
    ]);

    # run installation
    InstallationHelper::runInstallation();

    return true;
}
