<?php

namespace Dashed\DashedEcommerceExactonline\Models;

use Dashed\DashedEcommerceCore\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ExactonlineProduct extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'dashed__product_exactonline';

    protected $fillable = [
        'product_id',
        'exactonline_id',
        'error',
        'vat_code_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
