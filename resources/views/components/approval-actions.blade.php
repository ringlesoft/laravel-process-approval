<style>
    .lpa-container {
        display: flex;
        width: 100%;
        justify-content: center;
        padding: 15px;
    }

    .lpa-card-body {
        width: 100%;
        padding: 15px;
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        background: #fff;
    }

    .lpa-text-center {
        text-align: center;
    }

    table {
        width: 100%;
        font-size: 14px;
        color: #1f2937;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid #d1d5db;
        padding: 8px;
    }

    .approval-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .approved {
        background: #4ade80;
    }

    .rejected {
        background: #f87171;
    }

    .lpa-btn {
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .lpa-btn:hover {
        color: #fff;
    }

    .btn-green {
        background: #22c55e;
    }

    .btn-green:hover {
        background: #16a34a;
    }

    .btn-red {
        background: #ef4444;
    }

    .btn-red:hover {
        background: #dc2626;
    }

    .lpa-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 50;
        align-items: center;
        justify-content: center;
    }

    .lpa-modal-content {
        background: #fff;
        border-radius: 8px;
        width: 100%;
        max-width: 500px;
        position: relative;
        padding: 20px;
    }

    .lpa-modal-close {
        position: absolute;
        top: 10px;
        right: 18px;
        background: none;
        border: none;
        cursor: pointer;
        color: #9ca3af;
    }

    .lpa-modal-close:hover {
        color: #374151;
    }

    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 4px;
        border: 1px solid #d1d5db;
        font-size: 14px;
        margin-bottom: 15px;
    }

    .tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .approval-item:hover .tooltip {
        opacity: 1;
    }
</style>

@if($model->approvalsPaused !== true)
    <div class="lpa-container">
        <div class="lpa-card-body">
            <h6 class="lpa-text-center">{{ __('ringlesoft::approvals.approvals') }}</h6>

            @if($model->isSubmitted())
                <div class="approvals" style="margin-top: 15px;">
                    <table>
                        <thead>
                        <tr>
                            <th style="width: 50px;">{{ __('ringlesoft::approvals.by') }}:</th>
                            @foreach($modelApprovalSteps as $step)
                                <th style="width: {{(100 / count($modelApprovalSteps))}}%;" class="lpa-text-center">
                                    {{$step->role?->name ?? __('approvals.step') . ' '. $loop->iteration }}
                                </th>
                            @endforeach
                        </tr>
                        <tr>
                            <th>{{ __('ringlesoft::approvals.date') }}</th>
                            @foreach($modelApprovalSteps as $step)
                                <td>
                                    <div class="lpa-text-center">
                                        @if($currentApproval = $step->approval)
                                            <div class="approval-item" style="position: relative;">
                                                <div class="tooltip">
                                                    {{$currentApproval->comment ?? __('ringlesoft::approvals.no_comment') .'!'}}
                                                </div>
                                                @if($currentApproval->approval_action === \RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum::APPROVED->value)
                                                    @if($signature = $currentApproval->getSignature())
                                                        <div style="display: flex; justify-content: center;">
                                                            <img src="{{$signature}}" style="max-height: 50px;"
                                                                 alt="{{ __('ringlesoft::approvals.signature') }}">
                                                        </div>
                                                    @else
                                                        <div style="display: flex; justify-content: center;"
                                                             title="{{$currentApproval->comment}}"
                                                        >
                                                            <div class="approval-icon approved">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                     width="24"
                                                                     height="24" viewBox="0 0 24 24">
                                                                    <path
                                                                        d="M12,2C6.486,2,2,6.486,2,12s4.486,10,10,10s10-4.486,10-10S17.514,2,12,2z M12,20c-4.411,0-8-3.589-8-8s3.589-8,8-8 s8,3.589,8,8S16.411,20,12,20z"
                                                                        fill="#FFFFFF"></path>
                                                                    <path
                                                                        d="M9.999 13.587L7.7 11.292 6.288 12.708 10.001 16.413 16.707 9.707 15.293 8.293z"
                                                                        fill="#FFFFFF"></path>
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div style="display: flex; justify-content: center;">
                                                        <div style=""
                                                             title="{{$currentApproval->comment}}"
                                                             data-toggle="tooltip"
                                                             class="approval-icon rejected">
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
                                                                <i class="icon">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" height="24"
                                                                         viewBox="0 0 24 24" width="24">
                                                                        <path d="M0 0h24v24H0V0z" fill="none"
                                                                              opacity=".87"></path>
                                                                        <path
                                                                            d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.59-13L12 10.59 8.41 7 7 8.41 10.59 12 7 15.59 8.41 17 12 13.41 15.59 17 17 15.59 13.41 12 17 8.41z"
                                                                            fill="#FFFFFF"></path>
                                                                    </svg>
                                                                </i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                                <div>{{$currentApproval->user?->name}}</div>
                                                <div style="color: #6b7280; font-size: 12px;">
                                                    {{$currentApproval->created_at->format('d F, Y')}}
                                                </div>
                                            </div>
                                        @else
                                            <div style="padding: 32px 0;"></div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        </thead>
                    </table>
                </div>

                @if($model->approvalsPaused !== 'ONLY_ACTIONS')
                    <div class="approval-actions" style="margin-top: 15px;">
                        @if($nextApprovalStep && $model->approvalsDissabled !== 'ONLY_ACTIONS')
                            @if($userCanApprove)
                                <div style="display: flex; justify-content: space-between; gap: 10px;">
                                    <div style="color: #6b7280;">
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
                                    <div style="display: flex; gap: 8px;">
                                        @if($model->isRejected())
                                            <button class="lpa-btn btn-red" data-modal="discard-modal">
                                                {{ __('ringlesoft::approvals.discard') }}
                                            </button>
                                        @else
                                            <button class="lpa-btn btn-red" data-modal="reject-modal">
                                                {{ __('ringlesoft::approvals.reject') }}
                                            </button>
                                        @endif
                                        @if(!$model->isRejected())
                                            <button
                                                class="lpa-btn btn-red"
                                                type="button" data-modal="return-modal">
                                                {{ __('ringlesoft::approvals.return') }}
                                            </button>
                                        @endif
                                        <button
                                            class="lpa-btn btn-green"
                                            type="button" data-modal="approve-modal">
                                            {{$model->isRejected() ? __('ringlesoft::approvals.re_approve') : ucfirst(__('ringlesoft::approvals.'. strtolower($nextApprovalStep->action)) ?? __('ringlesoft::approvals.approve'))}}
                                        </button>
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
            @endif
        </div>
    </div>

    <!-- Modals -->
    @if($model->canBeApprovedBy(auth()?->user()))
        <div class="lpa-modal" id="approve-modal">
            <div class="lpa-modal-content">
                <button class="lpa-modal-close close-modal" data-modal="approve-modal">✕</button>
                <form method="post" action="{{route('ringlesoft.process-approval.approve', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <h3 style="margin-bottom: 20px; color: #6b7280;">{{ __('ringlesoft::approvals.approve_request') }}</h3>
                    <textarea name="comment"
                              class="approval-comment"
                              placeholder="{{ __('ringlesoft::approvals.write_comment') }} ({{ __('ringlesoft::approvals.optional') }})"></textarea>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="button" class="btn close-modal"
                                style="background: #fff; color: #6b7280; border: 1px solid #d1d5db;"
                                data-modal="approve-modal">
                            {{ __('ringlesoft::approvals.no_cancel') }}
                        </button>
                        <button type="submit" class="lpa-btn btn-green">
                            {{ucfirst(__('ringlesoft::approvals.'. strtolower($nextApprovalStep->action ?? 'Approve')))}}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Reject Modal -->
        <div class="lpa-modal" id="reject-modal">
            <div class="lpa-modal-content">
                <button class="lpa-modal-close close-modal" data-modal="reject-modal">✕</button>
                <form method="post" action="{{route('ringlesoft.process-approval.reject', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <h3 style="margin-bottom: 20px; color: #6b7280;">{{ __('ringlesoft::approvals.reject_request') }}</h3>
                    <textarea name="comment" placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                              class="approval-comment"
                              required></textarea>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="button" class="lpa-btn close-modal"
                                style="background: #fff; color: #6b7280; border: 1px solid #d1d5db;"
                                data-modal="reject-modal">
                            {{ __('ringlesoft::approvals.no_cancel') }}
                        </button>
                        <button type="submit" class="lpa-btn btn-red">
                            {{ __('ringlesoft::approvals.reject') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Return Modal -->
        <div class="lpa-modal" id="return-modal">
            <div class="lpa-modal-content">
                <button class="lpa-modal-close close-modal" data-modal="return-modal">✕</button>
                <form method="post" action="{{route('ringlesoft.process-approval.return', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <h3 style="margin-bottom: 20px; color: #6b7280;">{{ __('ringlesoft::approvals.return_request') }}</h3>
                    <textarea name="comment" placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                              class="approval-comment"
                              required></textarea>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="button" class="lpa-btn close-modal"
                                style="background: #fff; color: #6b7280; border: 1px solid #d1d5db;"
                                data-modal="return-modal">
                            {{ __('ringlesoft::approvals.no_cancel') }}
                        </button>
                        <button type="submit" class="lpa-btn btn-red">
                            {{ __('ringlesoft::approvals.return') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Discard Modal -->
        <div class="lpa-modal" id="discard-modal">
            <div class="lpa-modal-content">
                <button class="lpa-modal-close close-modal" data-modal="discard-modal">✕</button>
                <form method="post" action="{{route('ringlesoft.process-approval.discard', $model)}}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                    <h3 style="margin-bottom: 20px; color: #6b7280;">{{ __('ringlesoft::approvals.discard_request') }}</h3>
                    <textarea name="comment" placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                              class="approval-comment"
                              required></textarea>
                    <div style="display: flex; gap: 10px; justify-content: center;">
                        <button type="button" class="lpa-btn close-modal"
                                style="background: #fff; color: #6b7280; border: 1px solid #d1d5db;"
                                data-modal="discard-modal">
                            {{ __('ringlesoft::approvals.no_cancel') }}
                        </button>
                        <button type="submit" class="lpa-btn btn-red">
                            {{ __('ringlesoft::approvals.discard') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif

<script>
    document.querySelectorAll('[data-modal]').forEach(element => {
        element.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            modal.style.display = 'flex';
        });
    });

    document.querySelectorAll('.close-modal').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            modal.style.display = 'none';
        });
    });

    document.querySelectorAll('.approval-comment').forEach(textarea => {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                const form = this.closest('form');
                const submitButton = form.querySelector('button[type="submit"]');
                if (form.checkValidity()) { // Check form validity first
                    submitButton.click();
                }
            }
        });
    });
</script>
