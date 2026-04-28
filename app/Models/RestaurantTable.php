<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class RestaurantTable extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'capacity',
        'status',
        'shape',
        'position_x',
        'position_y',
        'width',
        'height',
        'floor',
        'merged_into_id',
        'merged_table_ids',
        'occupied_at',
        'reserved_at',
        'reservation_name',
        'reservation_phone',
        'reservation_notes',
        'current_order_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'merged_table_ids' => 'array',
        'occupied_at' => 'datetime',
        'reserved_at' => 'datetime',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the current order for this table.
     */
    public function currentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    /**
     * Get all orders that were placed at this table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    /**
     * Get the table this one is merged into.
     */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'merged_into_id');
    }

    /**
     * Get tables that are merged into this one.
     */
    public function mergedTables(): HasMany
    {
        return $this->hasMany(RestaurantTable::class, 'merged_into_id');
    }

    /**
     * Calculate turn time in minutes.
     */
    protected function turnTimeMinutes(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->occupied_at || $this->status !== 'occupied') {
                    return null;
                }
                return $this->occupied_at->diffInMinutes(now());
            }
        );
    }

    /**
     * Get formatted turn time.
     */
    protected function turnTimeFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $minutes = $this->turn_time_minutes;
                if ($minutes === null) {
                    return null;
                }
                
                if ($minutes < 60) {
                    return $minutes . 'm';
                }
                
                $hours = floor($minutes / 60);
                $mins = $minutes % 60;
                return $hours . 'h ' . $mins . 'm';
            }
        );
    }

    /**
     * Get the total capacity including merged tables.
     */
    protected function totalCapacity(): Attribute
    {
        return Attribute::make(
            get: function () {
                $total = $this->capacity;
                
                if (!empty($this->merged_table_ids)) {
                    $mergedCapacity = RestaurantTable::whereIn('id', $this->merged_table_ids)->sum('capacity');
                    $total += $mergedCapacity;
                }
                
                return $total;
            }
        );
    }

    /**
     * Check if table is available for seating.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active && !$this->merged_into_id;
    }

    /**
     * Mark table as occupied.
     */
    public function occupy(?int $orderId = null): void
    {
        $this->update([
            'status' => 'occupied',
            'occupied_at' => now(),
            'current_order_id' => $orderId,
            'reservation_name' => null,
            'reservation_phone' => null,
            'reservation_notes' => null,
            'reserved_at' => null,
        ]);
    }

    /**
     * Mark table as available.
     */
    public function release(): void
    {
        $this->update([
            'status' => 'available',
            'occupied_at' => null,
            'current_order_id' => null,
        ]);
    }

    /**
     * Mark table as dirty (needs cleaning).
     */
    public function markDirty(): void
    {
        $this->update([
            'status' => 'dirty',
            'occupied_at' => null,
            'current_order_id' => null,
        ]);
    }

    /**
     * Mark table as reserved.
     */
    public function reserve(string $name, ?string $phone = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'reserved',
            'reserved_at' => now(),
            'reservation_name' => $name,
            'reservation_phone' => $phone,
            'reservation_notes' => $notes,
        ]);
    }

    /**
     * Merge tables into this one.
     */
    public function mergeTables(array $tableIds): void
    {
        $existingMerged = $this->merged_table_ids ?? [];
        $newMerged = array_unique(array_merge($existingMerged, $tableIds));
        
        // Update merged tables to point to this one
        RestaurantTable::whereIn('id', $tableIds)->update([
            'merged_into_id' => $this->id,
            'status' => 'occupied',
        ]);
        
        $this->update([
            'merged_table_ids' => $newMerged,
        ]);
    }

    /**
     * Split/unmerge all tables.
     */
    public function splitTables(): void
    {
        if (!empty($this->merged_table_ids)) {
            RestaurantTable::whereIn('id', $this->merged_table_ids)->update([
                'merged_into_id' => null,
                'status' => 'available',
            ]);
        }
        
        $this->update([
            'merged_table_ids' => null,
        ]);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'available' => 'green',
            'occupied' => 'red',
            'reserved' => 'amber',
            'dirty' => 'zinc',
            default => 'zinc',
        };
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'occupied' => 'Occupied',
            'reserved' => 'Reserved',
            'dirty' => 'Needs Cleaning',
            default => ucfirst($this->status),
        };
    }
}
