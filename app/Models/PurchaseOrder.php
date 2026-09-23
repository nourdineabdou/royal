<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'supplier_id', 'purchase_request_id', 'event_id', 'reference', 'total_amount', 'paid_amount',
        'remaining_amount', 'payment_status', 'status', 'invoice_validated_at', 'invoice_validated_by',
    ];

    protected $casts = [
        'total_amount'         => 'decimal:2',
        'paid_amount'          => 'decimal:2',
        'remaining_amount'     => 'decimal:2',
        'invoice_validated_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function invoiceValidatedBy()
    {
        return $this->belongsTo(User::class, 'invoice_validated_by');
    }

    /** La demande d'achat d'origine, si ce BC en découle (voir PurchaseRequest). */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function supplierPayments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function supplierReturns()
    {
        return $this->hasMany(SupplierReturn::class);
    }

    public function isReceived()
    {
        return $this->status === 'received';
    }
}
