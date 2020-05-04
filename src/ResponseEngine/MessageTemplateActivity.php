<?php

namespace OpenDialogAi\ResponseEngine;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * @method static Builder forSubjectOrdered($orderId)
 */
class MessageTemplateActivity extends Activity
{
    public function scopeForSubjectOrdered(Builder $builder, $orderId): Builder
    {
        return $builder->where('subject_id', $orderId)->orderBy('id', 'desc');
    }
}
