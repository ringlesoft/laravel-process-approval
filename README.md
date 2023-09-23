# Laravel Process Approval

## Introduction

This package enables multi-level approval workflows for Eloquent models in your Laravel application. If you have models
that require review and sign-off from multiple approvers before execution, this package provides a flexible approval
process to meet that need.

## Installation

```bash
composer require ringunger/laravel-process-approval
```

## Usage

#### 1. Implement `AprovableModel` to your approvable model

```php
class FundRequest extends Model implements ApprovableModel
{

   // Your model content

}
```

#### 2. Apply the  `Approvable` trait to the model

```php
class FundRequest extends Model implements ApprovableModel
{
    use \RingleSoft\ProcessApproval\Traits\Approvable;
   // Your model content

}
```

#### 3. Implement the `onApprovalCompleted()` method and make sure it returns a boolean

```php
class FundRequest extends Model implements ApprovableModel
{
use \RingleSoft\ProcessApproval\Traits\Approvable;
   // Your model content
   
    public function onApprovalCompleted(ProcessApproval $approval): bool
    {
        // Write logic to be executed when the approval process is completed
        return true;
    }
}
```

#### 4. place the `<x-approval-actions >` component on the `show` page of your model

```php
    <x-ProcessApproval::approval-actions :model="$fundRequest" />
```

## Events
The package dispatches events during different stages of the approval workflow to allow hooking into the process.

- `RequestSubmittedEvent` - Dispatched when a new approval request is submitted.
- `RequestApprovedEvent` - Dispatched when an approval request is approved by an approver.
- `RequestRejectedEvent` - Dispatched when an approval request is rejected by an approver.
- `ProcessApprovalCompletedEvent` - Dispatched when the full approval workflow is completed, either approved or rejected.
