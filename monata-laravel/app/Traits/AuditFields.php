<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuditFields
{
    /**
     * Register the necessary event listeners for this trait.
     *
     * This registers the events for when a model is created, updated, or deleted.
     * It automatically sets the `creator_id` and `last_modifierId` when the model is created or updated.
     * It also automatically sets the `deleter_id` when the model is deleted.
     *
     * @return void
     */
    public static function bootAuditFields()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->creator_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->last_modifierId = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleter_id = Auth::id();
                $model->deletion_time = now();
                $model->setAttribute('deleter_id', Auth::id());
                $model->setAttribute('deletion_time', now());
            }
        });
    }
}
