<?php

namespace RingleSoft\LaravelProcessApproval\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\RedirectResponse;
use RingleSoft\LaravelProcessApproval\Models\ProcessApproval;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlowStep;

/**
 * @mixin Model
 */
interface ApprovableModel
{

    /**
     * Bypass the approval process for this model instance
     * @return bool
     */
    public function bypassApprovalProcess(): bool;

    /**
     * Enable auto-submit for this model instance
     * @return bool
     */
    public function enableAutoSubmit(): bool;

    /**
     * Returns the Type (Model Class) of this particular record
     * @return String
     */
    public static function getApprovableType(): String;

    /**
     * Returns the approval flow related to this record
     * @return ProcessApprovalFlow|Builder|null
     */
    public static function approvalFlow(): ProcessApprovalFlow|Builder|null;


    /**
     * Get only approved records
     * @return Builder
     */
    public static function approved(): Builder;

    /**
     * Get only rejected records
     * @return Builder
     */
    public static function rejected(): Builder;

    /**
     * Get only rejected records
     * @return Builder
     */
    public static function discarded(): Builder;

    /**
     * Get only non-submitted records
     * @return Builder
     */
    public static function nonSubmitted(): Builder;

    /**
     * Get only submitted records
     * @return Builder
     */
    public static function submitted(): Builder;


    /**
     * Returns a list of all approval(and rejections) done on this record
     * @return MorphMany
     */
    public function approvals(): MorphMany;


    /**
     * Returns true if approval for this record is completed
     * @return bool
     */
    public function isApprovalCompleted(): bool;

    /**
     * Returns true if the previous action on this record was rejection
     * @return bool
     */
    public function isRejected(): bool;

    /**
     * Returns true if the previous action on this record was discarded
     * @return bool
     */
    public function isDiscarded(): bool;

    /**
     * Get the next approval step for this record
     * @return ProcessApprovalFlowStep|null
     */
    public function nextApprovalStep(): ProcessApprovalFlowStep|null;

    /**
     * Get the previous approval step
     * @return ProcessApprovalFlowStep|null
     */
    public function previousApprovalStep(): ProcessApprovalFlowStep|null;

    /**
     * Submit the Request
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool|RedirectResponse
     */
    public function submit(Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse;

    /**
     * Approve the Request
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool|RedirectResponse
     */
    public function approve($comment = null, Authenticatable|null $user = null): ProcessApproval|bool|RedirectResponse;

    /**
     * Reject the request with comments
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool
     */
    public function reject($comment = null, Authenticatable|null $user = null): ProcessApproval|bool;

    /**
     * Discard the request with comments
     * @param null $comment
     * @param Authenticatable|null $user
     * @return ProcessApproval|bool
     */
    public function discard($comment = null, Authenticatable|null $user = null): ProcessApproval|bool;

    /**
     * Returns true if this record can be approved by the specified user
     * @param Authenticatable|null $user
     * @return bool|null
     */
    public function canBeApprovedBy(Authenticatable|null $user): bool|null;

    /**
     * A function run when approval is completed.
     * If this function returns false, the last approval is rolled back
     * @param ProcessApproval $approval
     * @return bool
     */
    public function onApprovalCompleted(ProcessApproval $approval): bool; // Must return true to apply approval

    /**
     * Get a collection of individuals yet to approve this record
     * @return Collection
     */
    public function getNextApprovers(): Collection;

    /**
     * Create approval flow for this record
     * @param String|null $name
     * @param array|null $steps
     * @return bool
     */
    public static function makeApprovable(array|null $steps = null, string|null $name = null):bool;
}

