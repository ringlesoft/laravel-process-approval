@if($model->approvalsPaused !== true)
<div {{ $attributes->class(['card']) }}>
    <div class="card-body">
        <h6 class="text-center">Approvals</h6>
        @if($model->isSubmitted())
            <div class="approvals">
                <table class="table table-sm table-bordered table-condensed mb-2">
                    <thead>
                    <tr>
                        <th style="width: 50px;">By:</th>
                        @foreach($modelApprovalSteps as $item)
                            <th style="width: {{(100 / count($modelApprovalSteps))}}%;" class="text-center">{{$item['step']->role?->name ?? 'Step '. $loop->iteration}}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Date</th>
                        @foreach($modelApprovalSteps as $item)
                            <td>
                                <div class="d-flex flex-column justify-content-center align-items-center">
                                    @if($currentApproval = $item['approval'])
                                        <div>
                                            @if($currentApproval->approval_action === \RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum::APPROVED->value)
                                                <div
                                                    data-bs-toggle="popover"
                                                    data-bs-placement="bottom"
                                                    data-bs-custom-class="header-info"
                                                    data-bs-content="{{$currentApproval->comment ?? 'No comment!'}}"
                                                    data-bs-original-title="Comment">
                                                    @if($signature = $currentApproval->getSignature())
                                                        <img src="{{$signature}}" class="img-fluid"
                                                             style="max-height: 50px;" alt="Signature">
                                                    @else
                                                        <div style="width: 40px; height: 40px;"
                                                             title="{{$currentApproval->comment}}" data-toggle="tooltip"
                                                             class="rounded bg-success text-white p-1 d-flex align-items-center justify-content-center">
                                                            <i class="icon">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                                     height="24" viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M12,2C6.486,2,2,6.486,2,12s4.486,10,10,10s10-4.486,10-10S17.514,2,12,2z M12,20c-4.411,0-8-3.589-8-8s3.589-8,8-8 s8,3.589,8,8S16.411,20,12,20z"
                                                                        fill="#FFFFFF"></path>
                                                                    <path
                                                                        d="M9.999 13.587L7.7 11.292 6.288 12.708 10.001 16.413 16.707 9.707 15.293 8.293z"
                                                                        fill="#FFFFFF"></path>
                                                                </svg>
                                                            </i>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div style="width: 40px; height: 40px;"
                                                     title="{{$currentApproval->comment}}" data-toggle="tooltip"
                                                     class="rounded bg-danger text-white p-1 d-flex align-items-center justify-content-center">
                                                    <i class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" height="24"
                                                             viewBox="0 0 24 24" width="24">
                                                            <path d="M0 0h24v24H0V0z" fill="none" opacity=".87"></path>
                                                            <path
                                                                d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.59-13L12 10.59 8.41 7 7 8.41 10.59 12 7 15.59 8.41 17 12 13.41 15.59 17 17 15.59 13.41 12 17 8.41z"
                                                                fill="#FFFFFF"></path>
                                                        </svg>
                                                    </i>
                                                </div>
                                            @endif
                                        </div>
                                        <div> {{$currentApproval->user?->name}}</div>
                                        <div
                                            class="small text-black-50">{{$currentApproval->created_at->format('d F, Y')}}</div>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    </thead>
                </table>
            </div>

            @if($model->approvalsPaused !== 'ONLY_ACTIONS')
            <div class="approval-actions">
                @if($nextApprovalStep)
                    @if($userCanApprove)
                        <div class="row align-content-between align-items-md-center">
                            <div class="col-12 col-md-7">
                                <div class="text-black-50">
                                    @if($model->isRejected())
                                        This request was rejected. You can re-approve this as
                                        <strong>{{$nextApprovalStep->role->name}}</strong>
                                    @else
                                        You Can approve this as <strong>{{$nextApprovalStep->role->name}}</strong>
                                    @endif

                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="w-100 d-flex justify-content-end gap-2 align-items-md-center">
                                    @if($model->isRejected())
                                        <button class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#modalDiscard">
                                            Discard
                                        </button>
                                    @else
                                        <button class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#modalReject">
                                            Reject
                                        </button>
                                    @endif
                                    <button class="btn btn-success" data-bs-toggle="modal"
                                            data-bs-target="#modalApprove">
                                        {{$model->isRejected() ? 'Re-Approve' : ucfirst(strtolower($nextApprovalStep->action) ?? 'Approve')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row align-content-between">
                            <div class="text-end flex-grow-1">
                            </div>
                            <div class="text-end">Waiting for approval from
                                <strong>{{$nextApprovalStep->role->name}}</strong></div>
                        </div>
                    @endif
                @else
                    <div>
                        @if($model->isDiscarded())
                            Discarded!
                        @else
                            Approval completed!
                        @endif
                    </div>
                @endif
            </div>
            @endif
        @else
            <div class="row align-content-between align-items-md-center">
                <div class="col-12 col-md-7">
                    <div class="text-black-50">
                        This document is not yet submitted.
                        @if($model->creator?->id === \Illuminate\Support\Facades\Auth::id())
                            <span>
                            You can submit this document for approvals.
                        </span>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-5">
                    <div class="w-100 d-flex justify-content-end gap-2 align-items-md-center">
                        @if($model)
                            <form action="{{route('ringlesoft.process-approval.submit', $model)}}" method="post">
                                @csrf
                                <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                @if($model->creator?->id === \Illuminate\Support\Facades\Auth::id())
                                    <button class="btn btn-success" type="submit">
                                        Submit
                                    </button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
<!-- Modal -->
<div class="modal fade" id="modalApprove" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="modalApproveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content" action="{{route('ringlesoft.process-approval.approve', $model)}}">
            @csrf
            <input type="hidden" name="user_id" value="{{auth()?->id()}}">
            <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
            <div class="modal-header">
                <h5 class="modal-title" id="modalApproveLabel">Approve Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="comment">Comment</label>
                    <textarea name="comment" id="approveComment" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Approve</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalReject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="modalRejectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content" action="{{route('ringlesoft.process-approval.reject', $model)}}">
            @csrf
            <input type="hidden" name="user_id" value="{{auth()?->id()}}">
            <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRejectLabel">Reject Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="approveComment">Comment</label>
                    <textarea required name="comment" id="approveComment" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDiscard" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="modalDiscardLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content" action="{{route('ringlesoft.process-approval.discard', $model)}}">
            @csrf
            <input type="hidden" name="user_id" value="{{auth()?->id()}}">
            <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDiscardLabel">Discard Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="alert alert-warning">
                    Discarding a process will end the approval process and mark it as discarded.
                </div>
                <div class="form-group">
                    <label for="approveComment">Comment</label>
                    <textarea required name="comment" id="approveComment" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Discard</button>
            </div>
        </form>
    </div>
</div>
@endif
