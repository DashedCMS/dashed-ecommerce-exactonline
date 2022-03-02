<?php

namespace Qubiqx\QcommerceEcommerceExactonline\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Qubiqx\QcommerceEcommerceCore\Models\Product;

class ExactonlineProduct extends Model
{
    use LogsActivity;

    protected static $logFillable = true;

    protected $table = 'qcommerce__product_exactonline';

    protected $fillable = [
        'product_id',
        'exactonline_id',
        'error',
        'vat_code_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
