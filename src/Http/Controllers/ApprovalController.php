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
use Illuminate\Support\Facades\Auth;
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
        $userModel = config('process_approval.users_model');
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => ['nullable'],
            'user_id' => ['sometimes', 'exists:' . (new $userModel)->getTable() . ',id']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            if ($approval = $model->submit($this->getUser($request->get('user_id')))) {
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
        $userModel = config('process_approval.users_model');
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:' . (new $userModel)->getTable() . ',id']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->approve($comment, $this->getUser($request->get('user_id')))) {
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
        $userModel = config('process_approval.users_model');
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => ['string', 'required', 'min:1'],
            'user_id' => ['sometimes', 'exists:' . (new $userModel)->getTable() . ',id']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->reject($comment, $this->getUser($request->get('user_id')))) {
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
        $userModel = config('process_approval.users_model');
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => ['string', 'required', 'min:1'],
            'user_id' => ['sometimes', 'exists:' . (new $userModel)->getTable() . ',id']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->return($comment, $this->getUser($request->get('user_id')))) {
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
        $userModel = config('process_approval.users_model');
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:' . (new $userModel)->getTable() . ',id']
        ];
        $request->validate($rules);
        try {
            $className = $request->input('model_name');
            $model = $className::findOrFail($id);
            $comment = $request->input('comment');
            if ($approval = $model->discard($comment, $this->getUser($request->get('user_id')))) {
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

    private function getUser($user_id): Authenticatable|null
    {
        return (config('process_approval.users_model'))::find($user_id) ?? Auth::user();
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
