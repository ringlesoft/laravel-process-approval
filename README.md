# Laravel Process Approval

## Introduction

This package enables multi-level approval workflows for Eloquent models in your Laravel application. If you have models
that require review and approval from multiple approvers before execution, this package provides a flexible approval
process to meet that need.

The package relies on an existing `Role` management. This can be a custom role management or another package such as
Spatie laravel permissions.

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
    <x-ringlesoft-approval-actions-bs :model="$fundRequest" />
```

Currently, the UI is implemented based on bootstrap. Support for tailwind and vanilla css will come soon.
Also you can publish the views and customize to suit your needs.

## Configuration

You can publish the configurations of this package `process_approval.php` and change variables to match your
requirement.

- `roles_model` - Specify the full class name of the model related to roles table. (for Spatie's laravel-permissions use
  the Spatie\Permissions\Models\Role)
- `users_model` - Specify the model that represents the authenticated users. (default is `App\Models\User`).
- `models_path` - Specify the default path for models in your application. (default is `App\Models`).
-

## Events

The package dispatches events during different stages of the approval workflow to allow hooking into the process.

- `ProcessSubmittedEvent` - Dispatched when a new approvable model is submitted.
- `ProcessApprovedEvent` - Dispatched when an approvable model is approved by an approver.
- `ProcessRejectedEvent` - Dispatched when an approvable model is rejected by an approver.
- `ProcessApprovalCompletedEvent` - Dispatched when the full approval workflow is completed, either approved or
  discarded.

## Helper Methods

This package adds multiple helper methods to the approvable models. These include:

- `approved` [Static]: This returns a builder that filters the model entries that are only approved (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::approved()->get();
    ```
- `rejected` [Static]:This returns a builder that filters the model entries that are only rejected (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::rejected()->get();
    ```
- `discarded` [Static]:This returns a builder that filters the model entries that are only discarded (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::discarded()->get();
    ```

- `isApprovalCompleted(): bool`: Checks if the approval process for the model is completed
- `isSubmitted(): bool`: Checks if the model has been submitted
- `isRejected(): bool`: Checks if the model has been rejected
- `isDiscarded(): bool`: Checks if the model has been discarded
- `nextApprovalStep(): null|ProcessApprovalFlowStep`: Returns the next approval step for the model
- `previousApprovalStep(): null|ProcessApprovalFlowStep`: Returns the previous approval step for the model
- `submit([user: Authenticatable|null = null]): bool|RedirectResponse|ProcessApproval`: Submits the model
- `approve([comment = null], [user: Authenticatable|null = null]): bool|RedirectResponse|ProcessApproval`: Approves the
  model
- `reject([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Rejects the model
- `discard([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Discards the model
- `canBeApprovedBy(user: Authenticatable|null): bool|null`: Checks if the model can currently be approved by the
  specified user.
- `onApprovalCompleted(approval: ProcessApproval): bool`: A callback method to be called when the approval process is
  completed.
  This method must be implemented and must return true for the last approval to be successful. Otherwise, the last
  approval will be rolled back.
- `getNextApprovers(): Collection`: Returns a list of users that are capable of approving the model at its current step.

## Contributing

I'll let you know when you can contribute 😜.

## License

Laravel Process Approval is open-source software released under the MIT License.
