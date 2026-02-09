<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\common\Racks;
use App\Models\common\ToolType;

class Tools extends Model
{
    protected $table = 'tools';
    protected $fillable = [
        'uid',
        'code',
        'name',
        'description',
        'acquisition_date',
        'acquisition_cost',
        'quantity',
        'min_quantity',
        'image',
        'racks_id',
        'tool_type_id',
        'status_id',
        'print_date',
        'print_barcode',
        'required_access_level',
        'barcode',
        'serial_number',
        'shelf_location',
    ];

    public function racks()
    {
        return $this->belongsTo(Racks::class);
    }

    public function tool_type()
    {
        return $this->belongsTo(ToolType::class);
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\common\Status::class);
    }

    public function loanTransactions()
    {
        return $this->hasMany(LoanTransaction::class, 'tool_id');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getShelfLocationDisplayAttribute()
    {
        // Prefer shelf_location, fallback to racks relationship
        if ($this->shelf_location) {
            return $this->shelf_location;
        }
        return $this->racks ? $this->racks->name : 'N/A';
    }

    public function scopeAvailable($query)
    {
        // Check if tool is available (not currently borrowed)
        return $query->whereDoesntHave('loanTransactions', function($q) {
            $q->where('status', 'Active');
        })->where('quantity', '>', 0);
    }

    public function scopeByAccessLevel($query, $accessLevel)
    {
        // Return tools that can be borrowed by this access level
        return $query->where('required_access_level', '<=', $accessLevel);
    }

    public function isAvailable()
    {
        // Check if tool has active loans
        $activeLoan = $this->loanTransactions()->where('status', 'Active')->exists();
        return !$activeLoan && $this->quantity > 0;
    }

    public function canBeBorrowedBy($toolCard)
    {
        // Check availability
        if (!$this->isAvailable()) {
            return [
                'can_borrow' => false,
                'message' => 'Tool is currently not available'
            ];
        }

        // Check access level
        if ($toolCard->access_level < $this->required_access_level) {
            return [
                'can_borrow' => false,
                'message' => 'Insufficient access level for this tool'
            ];
        }

        return [
            'can_borrow' => true,
            'message' => 'Tool can be borrowed'
        ];
    }
}
