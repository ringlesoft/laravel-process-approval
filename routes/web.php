<?php


use Illuminate\Support\Facades\Route;
use RingleSoft\LaravelProcessApproval\Http\Controllers\ApprovalController;

Route::group(['prefix' => 'process-approval', 'as' =>'ringlesoft.process-approval.'], static function() {
    Route::post('submit/{id}', [ApprovalController::class, 'submit'])->name('submit');
    Route::post('approve/{id}', [ApprovalController::class, 'approve'])->name('approve');
    Route::post('reject/{id}', [ApprovalController::class, 'reject'])->name('reject');
    Route::post('discard/{id}', [ApprovalController::class, 'discard'])->name('discard');
    Route::post('return/{id}', [ApprovalController::class, 'return'])->name('return');
});
