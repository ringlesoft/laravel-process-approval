# Laravel Process Approval

## Introduction

This package enables multi-level approval workflows for Eloquent models in your Laravel application. If you have models
that require review and approval from multiple approvers before execution, this package provides a flexible approval
process to meet that need.

The package relies on an existing `Role` management. This can be a custom role management or another package such as Spatie laravel permissions.

## Installation

```bash
composer require ringlesoft/laravel-process-approval
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
## Configuration
You can publish the configurations of this package `process_approval.php` and change variables to match your requirement.
- `roles_model` - Specify the full class name of the model related to roles table. (for Spatie's laravel-permissions use the Spatie\Permissions\Models\Role)
- `users_model` - Specify the model that represents the authenticated users. (default is `App\Models\User`).
- `models_path` - Specify the default path for models in your application. (default is `App\Models`).
- 
## Events
The package dispatches events during different stages of the approval workflow to allow hooking into the process.

- `ProcessSubmittedEvent` - Dispatched when a new approvable model is submitted.
- `ProcessApprovedEvent` - Dispatched when an approvable model is approved by an approver.
- `ProcessRejectedEvent` - Dispatched when an approvable model is rejected by an approver.
- `ProcessApprovalCompletedEvent` - Dispatched when the full approval workflow is completed, either approved or discarded.

## Contributing
  I'll let you know when you can contribute 😜.

## License
Laravel Process Approval is open-source software released under the MIT License.
