<?php

namespace RingleSoft\LaravelProcessApproval\Http\Controllers;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Contracts\UserResolver;
use RingleSoft\LaravelProcessApproval\Events\ApprovalNotificationEvent;

class ApprovalController extends Controller
{
    public function __construct(protected Redirector $redirector, protected Guard $auth)
    {
        $this->middleware('web'); // This is important for multi-tenancy
        $this->middleware(config('process_approval.approval_controller_middlewares'));
    }


    /**
     * Submit a process that is still pending
     * @param Request $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function submit(Request $request, $id): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser($request);
        $rules = [
            'model_name' => $this->modelNameRules(),
            'comment' => ['nullable']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            if ($approval = $model->submit($user)) {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.document_submitted'), $model);
            } else {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_submit_document'), $model, 'ERROR');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_submit_document') . $error, $model ?? null, 'ERROR');
        }
        if ($request->wantsJson()) {
            if ((empty($approval) || !is_object($approval)) && empty($error)) {
                $error = __('ringlesoft::messages.failed_to_submit_document');
            }
            return $this->jsonResponse($approval ?? null, $error ?? null, (empty($error) && ($approval ?? null)) ? 200 : 400);
        }
        return $this->redirector->back();
    }

    /**
     * Approve or Reject request
     * @param Request $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function approve(Request $request, $id): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser($request);
        $rules = [
            'model_name' => $this->modelNameRules(),
            'comment' => []
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->approve($comment, $user)) {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.document_approved'), $model);
            } else {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_approve_document'), $model, 'ERROR');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_approve_document') . ": $error", $model ?? null, 'ERROR');
        }

        if ($request->wantsJson()) {
            if ((empty($approval) || !is_object($approval)) && empty($error)) {
                $error = __('ringlesoft::messages.failed_to_approve_document');
            }
            return $this->jsonResponse($approval ?? null, $error ?? null, (empty($error) && ($approval ?? null)) ? 200 : 400);
        }
        return $this->redirector->back();
    }

    /**
     * Approve or Reject request
     * @param Request $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function reject(Request $request, $id): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser($request);
        $rules = [
            'model_name' => $this->modelNameRules(),
            'comment' => ['string', 'required', 'min:1']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->reject($comment, $user)) {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.document_rejected'), $model);
            } else {
                ApprovalNotificationEvent::dispatch(__('messages.failed_to_reject_document'), $model, 'ERROR');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_reject_document') . $error, $model ?? null, 'ERROR');
        }
        if ($request->wantsJson()) {
            if ((empty($approval) || !is_object($approval)) && empty($error)) {
                $error = __('ringlesoft::messages.failed_to_reject_document');
            }
            return $this->jsonResponse($approval ?? null, $error ?? null, (empty($error) && ($approval ?? null)) ? 200 : 400);
        }
        return $this->redirector->back();
    }

    /**
     * Return the model to the previous step
     * @param Request $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function return(Request $request, $id): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser($request);
        $rules = [
            'model_name' => $this->modelNameRules(),
            'comment' => ['string', 'required', 'min:1']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->return($comment, $user)) {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.document_returned'), $model);
            } else {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_return_document'), $model, 'ERROR');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_return_document') . $error, $model ?? null, 'ERROR');
        }
        if ($request->wantsJson()) {
            if ((empty($approval) || !is_object($approval)) && empty($error)) {
                $error = __('ringlesoft::messages.failed_to_return_document');
            }
            return $this->jsonResponse($approval ?? null, $error ?? null, (empty($error) && ($approval ?? null)) ? 200 : 400);
        }
        return $this->redirector->back();
    }

    /**
     * Discard The model
     * @param Request $request
     * @param $id
     * @return RedirectResponse|JsonResponse
     */
    public function discard(Request $request, $id): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser($request);
        $rules = [
            'model_name' => $this->modelNameRules(),
            'comment' => []
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->discard($comment, $user)) {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.document_discarded'), $model);
            } else {
                ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_discard_document'), $model, 'ERROR');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            ApprovalNotificationEvent::dispatch(__('ringlesoft::messages.failed_to_discard_document') . $error, $model ?? null, 'ERROR');
        }
        if ($request->wantsJson()) {
            if ((empty($approval) || !is_object($approval)) && empty($error)) {
                $error = __('ringlesoft::messages.failed_to_discard_document');
            }
            return $this->jsonResponse($approval ?? null, $error ?? null, (empty($error) && ($approval ?? null)) ? 200 : 400);
        }
        return $this->redirector->back();
    }

    /**
     * Resolve the acting user, aborting with 401 when there is none
     * @param Request $request
     * @return Authenticatable
     */
    private function resolveUser(Request $request): Authenticatable
    {
        $user = app(UserResolver::class)->resolve($request);
        abort_if($user === null, 401);
        return $user;
    }

    /**
     * Validation rules ensuring `model_name` refers to an approvable model class
     * @return array
     */
    private function modelNameRules(): array
    {
        return ['string', 'required', static function ($attribute, $value, $fail) {
            if (!is_string($value) || !is_a($value, ApprovableModel::class, true)) {
                $fail('The selected model is not approvable.');
            }
        }];
    }

    private function jsonResponse($data, $error = null, $status = null): JsonResponse
    {
        $response = [
            'data' => $data ?? null,
            'success' => (bool)$data,
        ];
        if ($error) {
            $response['error'] = $error;
        }
        return response()->json($response, $status ?? 200);
    }
}
