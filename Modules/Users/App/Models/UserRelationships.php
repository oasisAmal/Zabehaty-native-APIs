<?php

namespace Modules\Users\App\Models;

use Spatie\Activitylog\LogOptions;

trait UserRelationships
{
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }
}
