<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Models;

use Illuminate\Database\Eloquent\Model;
use Qubiqx\QcommerceEcommerceCore\Models\Order;
use Spatie\Activitylog\Traits\LogsActivity;

class ExactonlineOrder extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__order_exactonline';

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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
