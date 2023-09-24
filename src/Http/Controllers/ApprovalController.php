<?php

namespace RingleSoft\LaravelProcessApproval\Http\Controllers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;
use RingleSoft\LaravelProcessApproval\Events\ApprovalNotificationEvent;

class ApprovalController extends Controller
{
    public function __construct(protected  Redirector $redirector, protected Guard $auth)
    {

    }


    /**
     * Submit a process that is still pending
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function submit(Request $request, $id)
    {
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:users,id']
        ];
        $request->validate($rules);
        $className = $request->input('model_name');
        $model = $className::findOrFail($id);
        if($model->submit(auth()?->user())) {
            ApprovalNotificationEvent::dispatch('Document Submitted', $model);
        } else {
            ApprovalNotificationEvent::dispatch('Failed to submit document', $model, 'ERROR');
        }
        return $this->redirector->back();
    }

    /**
     * Approve or Reject request
     * @param Request $request
     * @param $id
     * @return void
     */
    public function approve(Request $request, $id)
    {
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:users,id']
        ];
        $request->validate($rules);
        $className = $request->input('model_name');
        $model = $className::findOrFail($id);
        $comment = $request->input('comment');
        if($model->approve($comment, $this->getUser($request->get('user_id')))) {
            ApprovalNotificationEvent::dispatch('Document approved successfully', $model);
        } else {
            ApprovalNotificationEvent::dispatch('Failed to approve document', $model, 'ERROR');
        }
        return $this->redirector->back();
    }

    /**
     * Approve or Reject request
     * @param Request $request
     * @param ApprovableModel $model
     * @return void
     */
    public function reject(Request $request, $id)
    {
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:users,id']
        ];
        $request->validate($rules);
        $className = $request->input('model_name');
        $model = $className::findOrFail($id);
        $comment = $request->input('comment');
        if($model->reject($comment, $this->getUser($request->get('user_id')))) {
            ApprovalNotificationEvent::dispatch('Document approved successfully', $model);
        } else {
            ApprovalNotificationEvent::dispatch('Failed to approve document', $model, 'ERROR');
        }
        return $this->redirector->back();
    }

    /**
     * Discard The model
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function discard(Request $request, $id)
    {
        $rules = [
            'model_name' => ['string', 'required'],
            'comment' => [],
            'user_id' => ['sometimes', 'exists:users,id']
        ];
        $request->validate($rules);
        $className = $request->input('model_name');
        $model = $className::findOrFail($id);
        $comment = $request->input('comment');
        if($model->discard($comment, $this->getUser($request->get('user_id')) )) {
            ApprovalNotificationEvent::dispatch('Document discarded successfully', $model);
        } else {
            ApprovalNotificationEvent::dispatch('Failed to discard document', $model, 'ERROR');
        }
        return $this->redirector->back();
    }

    private function getUser($user_id): Authenticatable|null
    {
        return (config('process_approval.users_model'))::find($user_id) ?? Auth::user();
    }
}
