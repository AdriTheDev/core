<?php

namespace App\Models\VisitTransferLegacy;

use App\Events\VisitTransferLegacy\ReferenceAccepted;
use App\Events\VisitTransferLegacy\ReferenceCancelled;
use App\Events\VisitTransferLegacy\ReferenceDeleted;
use App\Events\VisitTransferLegacy\ReferenceRejected;
use App\Events\VisitTransferLegacy\ReferenceUnderReview;
use App\Exceptions\VisitTransferLegacy\Reference\ReferenceNotRequestedException;
use App\Exceptions\VisitTransferLegacy\Reference\ReferenceNotUnderReviewException;
use App\Models\Model;
use App\Models\Mship\Account;
use App\Models\Mship\Note\Type;
use App\Models\Sys\Token;
use App\Models\Traits\HasStatus;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\VisitTransferLegacy\Reference.
 *
 * @property int $id
 * @property int $application_id
 * @property int $account_id
 * @property string|null $email
 * @property string|null $relationship
 * @property string|null $reference
 * @property int $status
 * @property string|null $status_note
 * @property \Carbon\Carbon|null $contacted_at
 * @property \Carbon\Carbon|null $reminded_at
 * @property \Carbon\Carbon|null $submitted_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Mship\Account $account
 * @property-read \App\Models\VisitTransferLegacy\Application $application
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Sys\Data\Change[] $dataChanges
 * @property-read mixed $is_accepted
 * @property-read mixed $is_rejected
 * @property-read mixed $is_requested
 * @property-read mixed $is_submitted
 * @property-read mixed $is_under_review
 * @property-read mixed $status_string
 * @property-read mixed $token
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Mship\Account\Note[] $notes
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \App\Models\Sys\Token $tokens
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference accepted()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference draft()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransferLegacy\Reference onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference pending()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference rejected()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference requested()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference status($status)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference statusIn($stati)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference submitted()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference underReview()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereContactedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereRemindedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereStatusNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\VisitTransferLegacy\Reference whereSubmittedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransferLegacy\Reference withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\VisitTransferLegacy\Reference withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Reference extends Model
{
    use HasStatus, Notifiable, SoftDeletes;

    protected $table = 'vt_reference';

    protected $primaryKey = 'id';

    protected $fillable = [
        'application_id',
        'account_id',
        'email',
        'relationship',
        'status',
        'status_note',
    ];

    protected $touches = ['application'];

    protected $casts = [
        'contacted_at' => 'datetime',
        'reminded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public $timestamps = false;

    protected $trackedEvents = ['created', 'updated', 'deleted', 'restored'];

    const STATUS_DRAFT = 10;

    const STATUS_REQUESTED = 30;

    const STATUS_UNDER_REVIEW = 50;

    const STATUS_ACCEPTED = 90;

    const STATUS_REJECTED = 95;

    const STATUS_CANCELLED = 100;

    const STATUS_DELETED = 101;

    public static $REFERENCE_IS_PENDING = [
        self::STATUS_DRAFT,
        self::STATUS_REQUESTED,
    ];

    public static $REFERENCE_IS_SUBMITTED = [
        self::STATUS_UNDER_REVIEW,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
    ];

    public static function scopePending($query)
    {
        return $query->whereNull('submitted_at');
    }

    public static function scopeStatus($query, $status)
    {
        return $query->where('status', '=', $status);
    }

    public static function scopeStatusIn($query, array $stati)
    {
        return $query->whereIn('status', $stati);
    }

    public static function scopeDraft($query)
    {
        return $query->status(self::STATUS_DRAFT);
    }

    public static function scopeRequested($query)
    {
        return $query->status(self::STATUS_REQUESTED);
    }

    public static function scopeSubmitted($query)
    {
        return $query->statusIn(self::$REFERENCE_IS_SUBMITTED);
    }

    public static function scopeUnderReview($query)
    {
        return $query->status(self::STATUS_UNDER_REVIEW);
    }

    public static function scopeAccepted($query)
    {
        return $query->status(self::STATUS_ACCEPTED);
    }

    public static function scopeRejected($query)
    {
        return $query->status(self::STATUS_REJECTED);
    }

    public function account()
    {
        return $this->belongsTo(\App\Models\Mship\Account::class);
    }

    public function application()
    {
        return $this->belongsTo(\App\Models\VisitTransferLegacy\Application::class);
    }

    public function tokens()
    {
        return $this->morphOne(Token::class, 'related');
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Mship\Account\Note::class, 'attachment');
    }

    public function getTokenAttribute()
    {
        return $this->tokens;
    }

    public function getIsSubmittedAttribute()
    {
        return in_array($this->state, self::$REFERENCE_IS_SUBMITTED);
    }

    public function getIsRequestedAttribute()
    {
        return $this->status == self::STATUS_REQUESTED;
    }

    public function getIsUnderReviewAttribute()
    {
        return $this->status == self::STATUS_UNDER_REVIEW;
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status == self::STATUS_ACCEPTED;
    }

    public function getIsRejectedAttribute()
    {
        return $this->status == self::STATUS_REJECTED;
    }

    public function getIsCancelledAttribute()
    {
        return $this->status == self::STATUS_CANCELLED;
    }

    public function getStatusStringAttribute()
    {
        switch ($this->attributes['status']) {
            case self::STATUS_DRAFT:
                return 'Draft';
            case self::STATUS_CANCELLED:
                return 'Cancelled';
            case self::STATUS_REQUESTED:
                return 'Requested';
            case self::STATUS_UNDER_REVIEW:
                return 'Under Review';
            case self::STATUS_ACCEPTED:
                return 'Accepted';
            case self::STATUS_REJECTED:
                return 'Rejected';
            case self::STATUS_DELETED:
                return 'Deleted';
        }
    }

    public function isStatusIn($stati)
    {
        return in_array($this->status, $stati);
    }

    public function generateToken()
    {
        $expiryTimeInMinutes = 1440 * 14; // 14 days

        return Token::generate('visittransfer_reference_request', false, $this, $expiryTimeInMinutes);
    }

    public function submit($referenceContent)
    {
        $this->guardAgainstReSubmittingReference();

        $this->reference = $referenceContent;
        $this->status = self::STATUS_UNDER_REVIEW;
        $this->submitted_at = \Carbon\Carbon::now();
        $this->save();

        event(new ReferenceUnderReview($this));
    }

    public function reject($publicReason = 'No reason was provided.', $staffReason = null, ?Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewReference();

        $this->status = self::STATUS_REJECTED;
        $this->status_note = $publicReason;
        $this->save();

        if ($staffReason) {
            $noteContent = 'VT Reference from '.$this->account->name." was rejected.\n".$staffReason;
            $note = $this->application->account->addNote(
                Type::isShortCode('VisitTransferLegacy')->first(),
                $noteContent,
                $actor,
                $this
            );
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ReferenceRejected($this));
    }

    public function delete()
    {
        // Set status to deleted
        $this->status = self::STATUS_DELETED;
        $this->save();

        return parent::delete();
    }

    public function cancel()
    {
        if ($this->isStatusIn(self::$REFERENCE_IS_PENDING) === false) {
            return;
        }
        $this->status = self::STATUS_CANCELLED;
        $this->save();
        $this->tokens()->delete();

        event(new ReferenceCancelled($this));
    }

    public function accept($staffComment = null, ?Account $actor = null)
    {
        $this->guardAgainstNonUnderReviewReference();

        $this->status = self::STATUS_ACCEPTED;
        $this->save();

        if ($staffComment) {
            $noteContent = 'VT Reference from '.$this->account->name." was accepted.\n".$staffComment;
            $note = $this->application->account->addNote('VisitTransferLegacy', $noteContent, $actor, $this);
            $this->notes()->save($note);
            // TODO: Investigate why this is required!!!!
        }

        event(new ReferenceAccepted($this));
    }

    /** Statistics */
    public static function statisticTotal()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_TOTAL', 60, function () {
            return self::count();
        });
    }

    public static function statisticRequested()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_REQUESTED', 60, function () {
            return self::requested()->count();
        });
    }

    public static function statisticSubmitted()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_SUBMITTED', 60, function () {
            return self::submitted()->count();
        });
    }

    public static function statisticUnderReview()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_UNDER_REVIEW', 60, function () {
            return self::underReview()->count();
        });
    }

    public static function statisticAccepted()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_ACCEPTED', 60, function () {
            return self::accepted()->count();
        });
    }

    public static function statisticRejected()
    {
        return Cache::remember('VT_REFERENCES_STATISTICS_REJECTED', 60, function () {
            return self::rejected()->count();
        });
    }

    /** Guards */
    private function guardAgainstReSubmittingReference()
    {
        if (! $this->is_requested) {
            throw new ReferenceNotRequestedException($this);
        }
    }

    private function guardAgainstNonUnderReviewReference()
    {
        if ($this->status != self::STATUS_UNDER_REVIEW) {
            throw new ReferenceNotUnderReviewException($this);
        }
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (self $reference) {
            $reference->tokens()->delete();
            event(new ReferenceDeleted($reference));
        });
    }
}
