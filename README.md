# Laravel Process Approval

## Introduction

This package enables multi-level approval workflows for Eloquent models in your Laravel application. If you have models
that require review and approval from multiple approvers before execution, this package provides a flexible approval
process to meet that need.

The package relies on an existing `Role` management. This can be a custom role management or another package such as
Spatie's `laravel permissions`.

<img src="https://ringlesoft.com/images/packages/approvals2.png" />

## Installation

#### 1. Install Using composer:

```bash
composer require ringlesoft/laravel-process-approval
```

#### 2. Run migration:

The package comes with four migrations. Run artisan migrate command before you start using the package.

```bash
php artisan migrate
```

#### 3. Create Approval flows and Steps

The package relies on Approval flows and steps on your default database. This is to enable multiple approval flows within the system. Yuo can
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
This will show a list of available Flows. Select the flow yow want to add steps to and then select the role and approval action.

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

Currently, the UI is implemented using `tailwind` or `bootstrap`. Support for vanilla CSS and JS will be available soon. You can switch between the two by modifying the `css_library` setting in the configuration file. Additionally, you have the option to publish the views and customize them to meet your specific requirements.


## Configuration

You can publish the configuration file of this package, `process_approval.php`, and modify the variables to align with
your specific requirements. If you wish to publish the files, use the following command:
```bash
php artisan vendor:publish --tag="Ringlesoft/LaravelProcessApproval"  
```
### Configurable parameters
- `roles_model` - Specify the full class name of the model related to roles table. (for Spatie's laravel-permissions use
  the Spatie\Permissions\Models\Role)
- `users_model` - Specify the model that represents the authenticated users. (default is `App\Models\User`).
- `models_path` - Specify the default namespace for models in your application. (default is `App\Models`).
- `approval_controller_middlewares` - Specify any middlewares you want to apply to the ApprovalController. (Normally it should be  `['auth']`).
- `css_library` - Specify the css library for styling the UI component (bootstrap/tailwind). (default is `Tailwind CSS`).

### Model Submitting

By default, the model becomes ready for approval when it is marked as "submitted". This provides the opportunity for
editing and other actions on the model before the approval procedure commences. This feature is particularly useful if
you wish to keep newly created models hidden from approvers until the creator submits them.

If you want the model to be auto-submitted upon creation, you can add the following property to the model:

```php
public bool autoSubmit = true;
```
Otherwise, the package will show a submit button on the show page of the model to enable the creator to submit the model.

### Pausing Approval process
Sometimes you may wish to interrupt the approval procedure by adding your own actions before continuing with approvals. 
You can pause approvals by adding a `pauseApprovals(): mixed` method to your Approvable Model. 

```php
public function pauseApprovals() {
    return true;
}
```
If this method returns true, the approval actions UI will disappear, and you will be able to implement your other logics.
If the method returns `'ONLY_ACTIONS'` the existing approvals will be displayed but approval actions will be hidden and disabled.

### Approval Signatures
If you want to use signatures for users, add the `getSignature()` method to your User model and make it return the signature of the user as image url.

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
If you want to display a summary of the approval process (normally when listing the models) you can use the `getApprovalSummaryUI()` method. 
This method returns html code with icons representing every approval step, `check` icon representing `Approved`, `times` icon representing `Rejected` and `exclamation` icon representing `Pending`.

```php
    $fundRequest->getApprovalSummaryUI();
```


## Events

The package dispatches events during different stages of the approval workflow to allow hooking into the process.

- `ProcessSubmittedEvent` - Dispatched when a new approvable model is submitted.
- `ProcessApprovedEvent` - Dispatched when an approvable model is approved by an approver.
- `ProcessRejectedEvent` - Dispatched when an approvable model is rejected by an approver.
- `ProcessDiscardedEvent` - Dispatched when an approvable model is discarded by an approver.
- `ProcessApprovalCompletedEvent` - Dispatched when the full approval workflow is completed, either approved or
  discarded.

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

- `submitted()` [Static]:This returns a builder that filters the model entries that are only submitted (
  returns `Illuminate\Database\Eloquent\Builder`)
  Example:
    ```php
        FundRequest::submitted()->get();
    ```
### Misc
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

## Contacts

Follow me on <a href="https://x.com/ringunger">Twitter</a>: <a href="https://x.com/ringunger">@ringunger</a>
