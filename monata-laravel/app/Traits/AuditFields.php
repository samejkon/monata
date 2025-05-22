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

    /**
     * Inserts a new record in the database with the audit fields.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $values
     * @return bool
     */
    public function scopeInsertWithAudit($query, array $values)
    {
        if (Auth::check()) {
            foreach ($values as &$value) {
                $value['creator_id'] = Auth::id();
            }
        }

        return $query->insert($values);
    }

    /**
     * Upserts records in the database with audit fields for creation and modification.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $values  The values to be inserted or updated.
     * @param  mixed  $uniqueBy  The unique constraint to determine if a record should be inserted or updated.
     * @param  array|null  $update  The fields to update if a match is found. Defaults to updating 'last_modifierId'.
     * @return bool  True if the operation was successful, false otherwise.
     */

    public function scopeUpsertWithAudit($query, array $values, $uniqueBy, $update = null)
    {
        $userId = Auth::check() ? Auth::id() : null;

        foreach ($values as &$value) {
            if (!array_key_exists('id', $value) || !$value['id']) {
                $value['creator_id'] = $userId;
            } else {
                $value['last_modifierId'] = $userId;
            }
        }

        $updateFields = $update ?: ['last_modifierId'];

        return $query->upsert($values, $uniqueBy, $updateFields);
    }
}
