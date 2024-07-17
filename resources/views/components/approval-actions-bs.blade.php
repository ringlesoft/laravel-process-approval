@if($model->approvalsPaused !== true)
    <div {{ $attributes->class(['card']) }}>
        <div class="card-body">
            <h6 class="text-center">{{ __('ringlesoft::approvals.approvals') }}</h6>
            @if($model->isSubmitted())
                <div class="approvals">
                    <table class="table table-sm table-bordered table-condensed mb-2">
                        <thead>
                        <tr>
                            <th style="width: 50px;">{{ __('ringlesoft::approvals.by') }}:</th>
                            @foreach($modelApprovalSteps as $item)
                                <th style="width: {{(100 / count($modelApprovalSteps))}}%;"
                                    class="text-center">{{$item['step']->role?->name ?? 'Step '. $loop->iteration}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <th>{{ __('ringlesoft::approvals.date') }}</th>
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
                                                        data-bs-content="{{$currentApproval->comment ??  __('ringlesoft::approvals.no_comment')}}"
                                                        data-bs-original-title="{{ __('ringlesoft::approvals.comment') }}">
                                                        @if($signature = $currentApproval->getSignature())
                                                            <img src="{{$signature}}" class="img-fluid"
                                                                 style="max-height: 50px;"
                                                                 alt="{{ __('ringlesoft::approvals.signature') }}">
                                                        @else
                                                            <div style="width: 40px; height: 40px;"
                                                                 title="{{$currentApproval->comment}}"
                                                                 data-toggle="tooltip"
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
                                                         title="{{$currentApproval->comment}}" data-bs-toggle="tooltip"
                                                         class="rounded bg-danger text-white p-1 d-flex align-items-center justify-content-center">
                                                        <i class="icon">
                                                            @if($currentApproval->approval_action === \RingleSoft\LaravelProcessApproval\Enums\ApprovalStatusEnum::OVERRIDDEN->value)
                                                                <svg clip-rule="evenodd" fill-rule="evenodd"
                                                                     stroke-linejoin="round" stroke-miterlimit="2"
                                                                     width="24" height="24" viewBox="0 0 24 24"
                                                                     xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="m12.002 21.534c5.518 0 9.998-4.48 9.998-9.998s-4.48-9.997-9.998-9.997c-5.517 0-9.997 4.479-9.997 9.997s4.48 9.998 9.997 9.998zm0-1.5c-4.69 0-8.497-3.808-8.497-8.498s3.807-8.497 8.497-8.497 8.498 3.807 8.498 8.497-3.808 8.498-8.498 8.498zm0-6.5c-.414 0-.75-.336-.75-.75v-5.5c0-.414.336-.75.75-.75s.75.336.75.75v5.5c0 .414-.336.75-.75.75zm-.002 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z"
                                                                        fill-rule="nonzero" fill="#fff"/>
                                                                </svg>
                                                            @else
                                                                <svg xmlns="http://www.w3.org/2000/svg" height="24"
                                                                     viewBox="0 0 24 24" width="24">
                                                                    <path d="M0 0h24v24H0V0z" fill="none"
                                                                          opacity=".87"></path>
                                                                    <path
                                                                        d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.59-13L12 10.59 8.41 7 7 8.41 10.59 12 7 15.59 8.41 17 12 13.41 15.59 17 17 15.59 13.41 12 17 8.41z"
                                                                        fill="#FFFFFF"></path>
                                                                </svg>
                                                            @endif
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
                                                {{ __('ringlesoft::approvals.request_rejected_re_approve') }}
                                                <strong>{{$nextApprovalStep->role->name}}</strong>
                                            @elseif($model->isReturned())
                                                {{ __('ringlesoft::approvals.request_returned_re_approve') }}
                                                <strong>{{$nextApprovalStep->role->name}}</strong>
                                            @elseif($model->isDiscarded())
                                                {{ __('ringlesoft::approvals.request_was_discarded') }}
                                            @else
                                                {{ __('ringlesoft::approvals.you_can_approve_this') }}
                                                <strong>{{$nextApprovalStep->role->name}}</strong>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5">
                                        <div class="w-100 d-flex justify-content-end gap-2 align-items-md-center">
                                            @if($model->isRejected())
                                                <button class="btn btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#modalDiscard">
                                                    {{ __('ringlesoft::approvals.discard') }}
                                                </button>
                                            @else
                                                <button class="btn btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#modalReject">
                                                    {{ __('ringlesoft::approvals.reject') }}
                                                </button>
                                            @endif

                                            @if(!$model->isRejected())
                                                <button class="btn btn-danger" data-bs-toggle="modal"
                                                        data-bs-target="#modalReturn">
                                                    {{ __('ringlesoft::approvals.return') }}
                                                </button>
                                            @endif
                                            <button class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#modalApprove">
                                                {{$model->isRejected() ? __('ringlesoft::approvals.re_approve') : ucfirst(__('ringlesoft::approvals.'. strtolower($nextApprovalStep->action)) ?? __('ringlesoft::approvals.approve'))}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="row align-content-between">
                                    <div class="text-end flex-grow-1">
                                    </div>
                                    <div class="text-end">{{ __('ringlesoft::approvals.waiting_for_approval_from') }}
                                        <strong>{{$nextApprovalStep->role->name}}</strong></div>
                                </div>
                            @endif
                        @else
                            <div>
                                @if($model->isDiscarded())
                                    {{ __('ringlesoft::approvals.discarded') }}!
                                @else
                                    {{ __('ringlesoft::approvals.approval_completed') }}!
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            @else
                <div class="row align-content-between align-items-md-center">
                    <div class="col-12 col-md-7">
                        <div class="text-black-50">
                            {{ __('ringlesoft::approvals.document_not_submitted') }}
                            @if($model->canBeSubmittedBy(\Illuminate\Support\Facades\Auth::user()))
                                <span>
                            {{ __('ringlesoft::approvals.you_can_submit') }}
                        </span>
                            @else
                                <span>
                                    {{ __('approvals.waiting_for_creator_submit') }}
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
                                    @if($model->canBeSubmittedBy(\Illuminate\Support\Facades\Auth::user()))
                                        <button class="btn btn-success" type="submit">
                                            {{ __('ringlesoft::approvals.submit') }}
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
    @if($model->canBeApprovedBy(auth()?->user()))
        <div class="modal fade" id="modalApprove" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="modalApproveLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content"
                      action="{{route('ringlesoft.process-approval.approve', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="modalApproveLabel">{{ __('ringlesoft::approvals.approve_request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="{{ __('ringlesoft::approvals.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="comment">{{ __('ringlesoft::approvals.comment') }}</label>
                            <textarea name="comment" id="approveComment" rows="3"
                                      placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                                      class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('ringlesoft::approvals.close') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('ringlesoft::approvals.approve') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalReject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="modalRejectLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content"
                      action="{{route('ringlesoft.process-approval.reject', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="modalRejectLabel">{{ __('ringlesoft::approvals.reject_request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="{{ __('ringlesoft::approvals.close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="approveComment">{{ __('ringlesoft::approvals.comment') }}</label>
                            <textarea required name="comment" id="rejectComment" rows="3"
                                      class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('ringlesoft::approvals.close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('ringlesoft::approvals.reject') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalDiscard" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="modalDiscardLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content"
                      action="{{route('ringlesoft.process-approval.discard', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="modalDiscardLabel">{{ __('ringlesoft::approvals.discard_request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="{{ __('ringlesoft::approvals.close') }}"></button>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-warning">
                            {{ __('ringlesoft::approvals.discarding_info') }}
                        </div>
                        <div class="form-group">
                            <label for="approveComment">{{ __('ringlesoft::approvals.comment') }}</label>
                            <textarea required name="comment" id="discardComment" rows="3"
                                      class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('ringlesoft::approvals.close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('ringlesoft::approvals.discard') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="modalReturn" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="modalReturnLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="post" class="modal-content"
                      action="{{route('ringlesoft.process-approval.return', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="modalReturnLabel">{{ __('ringlesoft::approvals.return_request') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="alert alert-warning">
                            {{ __('ringlesoft::approvals.returning_info') }}
                        </div>
                        <div class="form-group">
                            <label for="approveComment">{{ __('ringlesoft::approvals.comment') }}</label>
                            <textarea required name="comment" id="returnComment" rows="3"
                                      class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('ringlesoft::approvals.close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('ringlesoft::approvals.return') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
