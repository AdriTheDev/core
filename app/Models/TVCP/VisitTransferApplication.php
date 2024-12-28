<?php

namespace App\Models\TVCP;

use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitTransferApplication extends Model
{
    use HasFactory;

    protected $table = 'tvcp_visit_transfer_applications';

    protected $guarded = [];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $with = ['account'];

    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_VISIT = 'visit';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_ACCEPTED,
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_WITHDRAWN,
    ];

    public static function booted()
    {
        static::creating(function (VisitTransferApplication $application) {
            // populate initial state
            $application->state_history = json_encode([
                [
                    'status' => self::STATUS_SUBMITTED,
                    'timestamp' => now(),
                ],
            ]);
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transitionToStatus(string $status): void
    {
        $this->status = $status;

        $stateHistory = json_decode($this->state_history, true);

        $stateHistory[] = [
            'status' => $status,
            'timestamp' => now(),
        ];

        $this->state_history = json_encode($stateHistory);

        $this->save();
    }
}
