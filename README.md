# Laravel Process Approval
[![Latest Version on Packagist](https://img.shields.io/packagist/v/ringlesoft/laravel-process-approval.svg)](https://packagist.org/packages/ringlesoft/laravel-process-approval)
[![Total Downloads](https://img.shields.io/packagist/dt/ringlesoft/laravel-process-approval.svg)](https://packagist.org/packages/ringlesoft/laravel-process-approval)
[![PHP Version Require](https://poser.pugx.org/ringlesoft/laravel-process-approval/require/php)](https://packagist.org/ringlesoft/laravel-process-approval)
[![Dependents](https://poser.pugx.org/ringlesoft/laravel-process-approval/dependents)](https://packagist.org/packages/ringlesoft/laravel-process-approval)
***
## Introduction

This package enables multi-level approval workflows for Eloquent models in your Laravel application. If you have models
that require review and approval from multiple approvers before execution, this package provides a flexible approval
process to meet that need.
> Laravel 10.0 or later

The package relies on an existing `Role` management. This can be a custom role management or another package such as
Spatie's `laravel permissions`.

<img src="https://ringlesoft.com/images/packages/approvals2.png" alt="Approvals Screenshot" />

## Installation

#### 1. Install Using composer:

```bash
composer require ringlesoft/laravel-process-approval
```

#### 2. Publish Files (Optional)

This package provides publishable files that include configuration, migrations and views. You can publish these files
using the following command:

```bash
php artisan vendor:publish --provider="RingleSoft\LaravelProcessApproval\LaravelProcessApprovalServiceProvider" 
```

You can publish specific files by providing the ```--tag``` option within the publish command. Available options
are ```approvals-migrations```, ```approvals-config```, ```approvals-views```, ```approvals-translations```. <br> For example:

```bash
php artisan vendor:publish --provider="RingleSoft\LaravelProcessApproval\LaravelProcessApprovalServiceProvider" --tag="approvals-migrations" 
```

#### 3. Run migration:

The package comes with four migration files. Run artisan migrate command before you start using the package.

```bash
php artisan migrate
```

#### 4. Create Approval flows and Steps

The package relies on Approval flows and steps on your default database. This is to enable multiple approval flows
within the system. You can
implement your own way of creating and managing the flows. However, there are available command-line functions to help
you get started easily.

##### i. Creating a new flow

To create a new flow, Run the following command on your terminal.

```bash
 php artisan process-approval:flow add FundRequest
```

#### ii. Creating a step for the flow

```bash
php artisan process-approval:step add  
```

This will show a list of available Flows. Select the flow yow want to add steps to and then select the role and approval
action.

#### iii. Deleting a flow

```bash
php artisan process-approval:flow remove  
```

This will show a list of available flows. Select the step you want to delete and hit enter.

#### iv. Deleting a step

```bash
php artisan process-approval:step remove  
```

This will show a list of available steps. Select the step you want to delete and hit enter.

#### v. Listing all flows

```bash
php artisan process-approval:flow list  
```

This will show a list of all available flows and steps

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

#### 3. Implement the `onApprovalCompleted()` method.

This package relies on one callback method in your model to commit the last approval and mark the approval process as
completed. You should implement this method and return `true` to finalize the approval or `false` to roll back the last
approval. This is useful in the case of performing specific tasks when the approval procedure is completed.

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

#### 4. Place the `<x-ringlesoft-approval-actions>` component on the show page of your model and provide the model instance using the `model` parameter.

```php
    <x-ringlesoft-approval-actions :model="$fundRequest" />
```

Currently, the UI is implemented using `tailwind` or `bootstrap`. Support for vanilla CSS and JS will be available soon.
You can switch between the two by modifying the `css_library` setting in the configuration file. Additionally, you have
the option to publish the views and customize them to meet your specific requirements.

## Configuration

You can publish the configuration file of this package, `process_approval.php`, and modify the variables to align with
your specific requirements. If you wish to publish the files, use the following command:

```bash
php artisan vendor:publish --provider="RingleSoft\LaravelProcessApproval\LaravelProcessApprovalServiceProvider" --tag="config"
```

### Configurable parameters

- `roles_model` - Specify the full class name of the model related to roles table. (default is Spatie's laravel-permissions  (`Spatie\Permissions\Models\Role`))
- `users_model` - Specify the model that represents the authenticated users. (default is `App\Models\User`).
- `models_path` - Specify the default namespace for models in your application. (default is `App\Models`).
- `approval_controller_middlewares` - Specify any middlewares you want to apply to the ApprovalController. (Normally it
  should be  `['auth']`).
- `css_library` - Specify the css library for styling the UI component (bootstrap/tailwind). (default
  is `Tailwind CSS`).
- `multi_tenancy_field` - Specify the multi-tenancy field in the users table. (default is `tenant_id`)

### Model Submitting

By default, the model becomes ready for approval when it is marked as "submitted". This provides the opportunity for
editing and other actions on the model before the approval procedure commences. This feature is particularly useful if
you wish to keep newly created models hidden from approvers until the creator submits them.

If you want the model to be auto-submitted upon creation, you can add the following property to the model:

```php
public bool $autoSubmit = true;
```

Otherwise, the package will show a submit button on the show page of the model to enable the creator to submit the
model.

### Pausing Approval process

Sometimes you may wish to interrupt the approval procedure by adding your own actions before continuing with approvals.
You can pause approvals by adding a `pauseApprovals(): mixed` method to your Approvable Model.

```php
public function pauseApprovals() {
    return true;
}
```

If this method returns true, the approval actions UI will disappear, and you will be able to implement your other
logics.
If the method returns `'ONLY_ACTIONS'` the existing approvals will be displayed but approval actions will be hidden and
disabled.

### Approval Signatures

If you want to use signatures for users, add the `getSignature()` method to your User model and make it return the
signature of the user as image url.

```php
Class User extends Model {
    ...
    
    public function getSignature(){
        return $this->signature_path; // Return the path to user's signature
    }
}
```

If not specified, the package will display `check` icon for approval and `times` icon for rejection.

### Approval Summary

If you want to display a summary of the approval process (normally when listing the models) you can use
the `<x-ringlesoft-approval-status-summary>` component.
This component returns html code with icons representing every approval step: `check` icon representing `Approved`, `times`
icon representing `Rejected` or `Discarded` and `exclamation` icon representing `Pending`.

```php
    $fundRequest->getApprovalSummaryUI();
```

## Events

The package dispatches events during different stages of the approval workflow to allow hooking into the process.

- `ProcessSubmittedEvent` - Dispatched when a new approvable model is submitted.
- `ProcessApprovedEvent` - Dispatched when an approvable model is approved by an approver.
- `ProcessRejectedEvent` - Dispatched when an approvable model is rejected by an approver.
- `ProcessReturnedEvent` - Dispatched when an approvable model is returned back to the previous step by an approver.
- `ProcessDiscardedEvent` - Dispatched when an approvable model is discarded by an approver.
- `ProcessApprovalCompletedEvent` - Dispatched when the full approval workflow is completed, either approved or
- `ApprovalNotificationEvent` - Dispatches during approval actions with the notification message about what happened.
  discarded.

### Showing Notifications

To display approval notifications, subscribe to the `ApprovalNotificationEvent` event.

#### 1. Create a Listener:

Generate a listener for the event using artisan command:

```bash
php artisan make:listener ApprovalNotificationListener --event=\\RingleSoft\\LaravelProcessApproval\\Events\\ApprovalNotificationEvent
```

#### 2. Implement Listener Logic:
Inside the generated ApprovalNotificationListener class, implement the logic within the `handle()` method. 
This method will execute whenever the ApprovalNotificationEvent event is triggered. Customize the notification content and delivery method as per your application's requirements.

Example listener implementation:

```php
class ApprovalNotificationListener
{
    ...
    /**
     * Handle the event.
     */
    public function handle(ApprovalNotificationEvent $event): void
    {
        session()->flash('success', $event->message);
    }
}
```

#### 3. Register Listener:
Register the listener in your `EventServiceProvider` class to link the `ApprovalNotificationEvent` event with your `ApprovalNotificationListener`:

```php
protected $listen = [
    ApprovalNotificationEvent::class => [
        ApprovalNotificationListener::class,
    ],
];
```
Use your own approach to display notification from `session()`


### Notifying Approvers
To notify approvers when a document is awaiting their approval, you can subscribe to the `ProcessSubmittedEvent` and `ProcessApprovedEvent` events and send notifications to them.

Here is an example of how to send notifications to the next approvers within the `ProcessSubmittedListener` listener:
```php
    public function handle(ProcessSubmittedEvent $event): void
    {
        $nextApprovers = $event->approvable->getNextApprovers();
        foreach ($nextApprovers as $nextApprover) {
            $nextApprover->notify(new AwaitingApprovalNotification($event->approvable));
        }
    }
```


## Helper Methods

This package adds multiple helper methods to the approvable models. These include:

### Filters

- `approved()` [Static]: This returns a builder that filters the model entries that are only approved (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::approved()->get();
    ```
- `rejected()` [Static]:This returns a builder that filters the model entries that are only rejected (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::rejected()->get();
    ```
- `discarded()` [Static]:This returns a builder that filters the model entries that are only discarded (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::discarded()->get();
    ```
- `returned()` [Static]:This returns a builder that filters the model entries that are only returned (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::returned()->get();
    ```

- `submitted()` [Static]:This returns a builder that filters the model entries that are only submitted (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::submitted()->get();
    ```
  
### Actions
- `submit([user: Authenticatable|null = null]): bool|RedirectResponse|ProcessApproval`: Submits the model
- `approve([comment = null], [user: Authenticatable|null = null]): bool|RedirectResponse|ProcessApproval`: Approves the
  model
- `reject([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Rejects the model
- `return([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Returns the model to the previous step
- `discard([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Discards the model
- `return([comment = null], [user: Authenticatable|null = null]): bool|ProcessApproval`: Returns the model to the previous step


### Misc
- `isApprovalCompleted(): bool`: Checks if the approval process for the model is completed
- `isSubmitted(): bool`: Checks if the model has been submitted
- `isRejected(): bool`: Checks if the model has been rejected
- `isDiscarded(): bool`: Checks if the model has been discarded
- `isReturned(): bool`: Checks if the model has been returned back to the previous step
- `nextApprovalStep(): null|ProcessApprovalFlowStep`: Returns the next approval step for the model
- `previousApprovalStep(): null|ProcessApprovalFlowStep`: Returns the previous approval step for the model
- `canBeApprovedBy(user: Authenticatable|null): bool|null`: Checks if the model can currently be approved by the
  specified user.
- `onApprovalCompleted(approval: ProcessApproval): bool`: A callback method to be called when the approval process is
  completed.
  This method must be implemented and must return true for the last approval to be successful. Otherwise, the last
  approval will be rolled back.
- `getNextApprovers(): Collection`: Returns a list of users that are capable of approving the model at its current step.

#### Relations
- `approvals(): morphMany` - Returns all approvals of the model
- `lastApproval(): morphOne` - Returns the last approval (`Models\ProcessApproval`) of the model
- `approvalStatus(): morphOne` - Returns the status object (`Models\ProcessApprovalStatus`) of the model

## Seeding
If you want to seed your approval flows to the database, this package provides a static method `makeApprovable(): bool` to create a new approval flow for a model. 
This method can be used to seed the database with the necessary approval flows and steps for a model. 

The method accepts two parameters:


- `$steps`: An array defining the roles to be used as approval steps.
- `$name`: (optional) The name of the approval flow.

#### Basic usage:
When the first parameter is a flat array of integers, the method creates a new approval flow with the array items as `role_id` and sets `ApprovalTypeEnum::APPROVE` as the default action for each step.
```php
    FundRequest::makeApprovable([1,2,3]);
```
#### Advanced usage:

When the first parameter is an associative array of `[int => ApprovalTypeEnum, ...]`, the method creates a new approval flow with the array keys (`int`) as `role_id` and the values (`ApprovalTypeEnum`) as the corresponding action.
```php
    FundRequest::makeApprovable([
        1 => ApprovalTypeEnum::APPROVE,
        3 => ApprovalTypeEnum::CHECK
    ]);
```
#### Complex usage:
When the first parameter is an array of arrays, the method creates a new approval flow with steps that accepts `[role_id => int, action => ApprovalTypeEnum]` from the sub-arrays. 
```php
    FundRequest::makeApprovable([
                [
                    'role_id' => 2,
                    'action' => ApprovalTypeEnum::CHECK->value
                ],
                [
                    'role_id' => 1,
                    'action' => ApprovalTypeEnum::CHECK->value
                ],
                [
                    'role_id' => 1,
                    'action' => ApprovalTypeEnum::APPROVE->value
                ]
            ]
        );
```
This option enables you to create a flow with multiple steps for the same role, each step having a different action or occurrence.

## Multi-Tenancy
This package supports multi-tenancy by configuring a column in the users table. You can specify the column name using the `multi_tenancy_field` configuration option. 
When the logged-in user is has the `tenant_id` field set, the package will use that value to filter the approval steps.
With this you can have one approval flow with different steps for different tenants.

## Testing
To test this package, switch to the `tests` branch and run `composer install` to install the dependencies and `vendor/bin/testbench package:test` to run the tests.

## Contributing

I'll let you know when you can contribute 😜.

## License

Laravel Process Approval is open-source software released under the MIT License.

## Contacts

Follow me on <a href="https://x.com/ringunger">X</a>: <a href="https://x.com/ringunger">@ringunger</a><br>
Email me: <a href="mailto:ringunger@gmail.com">ringunger@gmail.com</a>
