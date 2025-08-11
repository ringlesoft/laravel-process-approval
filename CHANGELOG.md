# Changelog
All notable changes to this project will be documented in this file.

## [1.1.2] - 2025-08-11
### Added
- Blade template updates and approval action improvements
- Overall Query optimization and eager loading improvements
- Performance and code quality improvements 
### Fixed
- Multitenancy field issue and conditional logic corrections



## [1.1.1] - 2025-04-03
### Added
- Support for Bootstrap 3 and 4
- New UI independent of CSS Libraries (beta)
- Introduced bypassApprovalProcess and enableAutoSubmit method for approvable models
- Fixed Issue ProcessApprovalCompletedEvent did not receive `ApprovableModel`
- Multiple improvements and bug fixes

## [1.1.0] - 2025-03-20
### Added
- Support for Laravel 12
- Added a static method `requiresApproval()` to check if the particular model will require approval when submitted
- Added `bypassApprovalProcess` method for bypassing model's approval process programmatically
- Added `enableAutoSubmit` method for enabling model's autoSubmit programmatically
### Fixed
- Fixed a bug where status of a model would show APPROVED even before the approval process is finished
- Multiple bug fixes and improvements

## [1.0.9] - 2024-10-07
### Fixed
- Multiple improvements
- Resolved issue #41
### Added
- deprecated `getApprovalSummaryUi()`
- Now you can throw a custom Exception within the `onApprovalCompletedCallback()` to be able to customize the notifications shown to the user when approval fails.

## [1.0.8] - 2024-07-15
### Added
- Support for Multi-Tenancy ([#24](https://github.com/ringlesoft/laravel-process-approval/issues/24))
- Ability to return a record to the previous step ([#18](https://github.com/ringlesoft/laravel-process-approval/issues/18))
- Method for seeding the database with approval flows and steps
- Support for Multilanguage

### Fixed
- Resolved compatibility issue with PostgreSQL by removing backticks from SQL queries

### Changed
- Deprecated `getApprovalSummaryUI()` method in favor of `<x-ringlesoft-approval-status-summary>` component
- `web` middleware is applied to the ApprovalController by default

### Additional
- Added testing branch `tests`

## [1.0.7] - 2024-04-17
- Support for Laravel 11 [#19](https://github.com/ringlesoft/laravel-process-approval/issues/19).
- Multiple improvements
- A few bug fixes

## [1.0.6] - 2024-02-03
- Now you can publish specific files (approvals-config, approvals-migrations and approvals-views) using the `--tag` options.

## [1.0.5] - 2023-11-23
- A few bug fixes

## [1.0.4] - 2023-11-15
- Now you can specify middleware to be applied to the ApprovalController
- Introduced API access. You can now submit your own approval form via api for SPAs.
- More exception classes
- A lot of bug fixes

## [1.0.3] - 2023-11-13
- Minor bug fixes

## [1.0.2] - 2023-11-13
- Minor bug fixes

## [1.0.1] - 2023-11-01
- Tailwind CSS Support: We've added support for Tailwind CSS as an optional choice for the UI component. Customize your user interface with ease.
- Laravel/Prompts for CLI: We now provide Laravel prompts for the Command-line Interface (CLI), simplifying your interactions with the package through the command line.
- New Facade: A new Facade is introduced, allowing you to work with Approval Flows programmatically, providing more flexibility in your workflows.
- This release includes multiple bug fixes and general improvements to enhance the stability and functionality of the package.
