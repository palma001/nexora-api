<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    use SoftDeletes;

    public static function bootAuditable()
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleting(function ($model) {
            if (!$model->isForceDeleting() && Auth::check()) {
                $model->deleted_by = Auth::id();
            }
        });

        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                $model->logAudit('force_deleted');
            } else {
                $model->logAudit('deleted');
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logAudit('restored');
            });
        }
    }

    public function logAudit(string $event)
    {
        $oldValues = $event === 'updated' ? array_intersect_key($this->getRawOriginal(), $this->getDirty()) : null;
        $newValues = $event === 'updated' ? $this->getDirty() : ($event === 'created' ? $this->getAttributes() : null);

        // Remove sensitive fields or large fields if necessary
        $sensitiveFields = ['password', 'remember_token'];
        if ($oldValues) $oldValues = array_diff_key($oldValues, array_flip($sensitiveFields));
        if ($newValues) $newValues = array_diff_key($newValues, array_flip($sensitiveFields));

        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => static::class,
            'auditable_id' => $this->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}
