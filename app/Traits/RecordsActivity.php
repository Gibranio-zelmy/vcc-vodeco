<?php

namespace App\Traits;

use App\Models\LogHistory;

trait RecordsActivity
{
    public static function bootRecordsActivity()
    {
        static::created(function ($model) {
            $model->recordLog('CREATE', $model->getAttributes());
        });
        static::updated(function ($model) {
            $model->recordLog('UPDATE', $model->getChanges());
        });
        static::deleted(function ($model) {
            $model->recordLog('DELETE', $model->getAttributes());
        });
    }

    protected function recordLog($action, $changes)
    {
        if (auth()->check()) {
            LogHistory::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => class_basename($this),
                'changes' => json_encode($changes),
                // Mencatat presisi waktu entry dan exit
                'entry_timestamp' => $this->created_at ?? now(), 
                'exit_timestamp' => now(),
            ]);
        }
    }
}