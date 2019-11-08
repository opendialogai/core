<?php


namespace OpenDialogAi\ConversationBuilder;


use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

/**
 * @method static Builder forSubjectOrdered($orderId)
 */
class ConversationActivity extends Activity
{
    public function scopeForSubjectOrdered(Builder $builder, $orderId): Builder
    {
        return $builder->where('subject_id', $orderId)->orderBy('id', 'desc');
    }
}
