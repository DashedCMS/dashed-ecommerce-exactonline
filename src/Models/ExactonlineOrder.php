<?php

namespace Dashed\DashedEcommerceExactonline\Models;

use Dashed\DashedEcommerceCore\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExactonlineOrder extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__order_exactonline';

    protected $fillable = [
        'order_id',
        'exactonline_id',
        'error',
        'pushed',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
