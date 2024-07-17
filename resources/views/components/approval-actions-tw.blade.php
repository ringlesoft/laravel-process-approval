@if($model->approvalsPaused !== true)
    <div {{ $attributes->class(['flex w-full justify-center p-3']) }} >
        <div class="card-body w-full p-3 overflow-x-auto  sm:rounded-lg border border-gray-300 ">
            <h6 class="text-center">{{ __('ringlesoft::approvals.approvals') }}</h6>
            @if($model->isSubmitted())
                <div class="approvals relative mt-3">
                    <table
                        class="w-full text-sm text-left text-gray-800 dark:text-gray-400 mb-2 border border-collapse">
                        <thead>
                        <tr>
                            <th class="border p-2" style="width: 50px;">{{ __('ringlesoft::approvals.by') }}:</th>
                            @foreach($modelApprovalSteps as $item)
                                <th style="width: {{(100 / count($modelApprovalSteps))}}%;"
                                    class="text-center border">{{$item['step']->role?->name ?? __('approvals.step') . ' '. $loop->iteration }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="p-2">{{ __('ringlesoft::approvals.date') }}</th>
                            @foreach($modelApprovalSteps as $item)
                                <td class="border p-2">
                                    <div class="text-center">
                                        @if($currentApproval = $item['approval'])
                                            <div class="relative group">
                                                <div
                                                    class="absolute bottom-0 left-1/2 -translate-x-1/2 transform opacity-0 scale-75 origin-bottom group-hover:opacity-100 transition-all duration-300 z-10 px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                                    {{$currentApproval->comment ?? __('ringlesoft::approvals.no_comment') .'!' }}
                                                </div>
                                                @if($currentApproval->approval_action === \RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum::APPROVED->value)
                                                    <div class="relative group">
                                                        <div
                                                            class="absolute bottom-0 left-1/2 -translate-x-1/2 transform opacity-0 scale-75 origin-bottom group-hover:opacity-100 transition-all duration-300 z-10 px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                                            {{$currentApproval->comment ?? __('ringlesoft::approvals.no_comment') .'!'}}
                                                        </div>
                                                        @if($signature = $currentApproval->getSignature())
                                                            <div class="flex justify-center">
                                                                <img src="{{$signature}}" class="img-fluid"
                                                                     style="max-height: 50px;"
                                                                     alt="{{ __('ringlesoft::approvals.signature') }}">
                                                            </div>
                                                        @else
                                                            <div class="flex justify-center">
                                                                <div style="width: 40px; height: 40px;"
                                                                     title="{{$currentApproval->comment}}"
                                                                     data-toggle="tooltip"
                                                                     class="rounded bg-green-400 flex justify-center items-center h-full">
                                                                    <i class="icon">
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
                                                                    </i>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="flex justify-center">
                                                        <div style="width: 40px; height: 40px;"
                                                             title="{{$currentApproval->comment}}" data-toggle="tooltip"
                                                             class="rounded bg-red-400 flex justify-center items-center h-full">
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
                                            </div>
                                            <div> {{$currentApproval->user?->name}}</div>
                                            <div
                                                class="small text-gray-500">{{$currentApproval->created_at->format('d F, Y')}}</div>
                                        @else
                                            <div class="py-8"></div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                        </thead>
                    </table>
                </div>
                @if($model->approvalsPaused !== 'ONLY_ACTIONS')
                    <div class="approval-actions w-full mt-3">
                        @if($nextApprovalStep && $model->approvalsDissabled !== 'ONLY_ACTIONS')
                            @if($userCanApprove)
                                <div class="flex justify-between">
                                    <div class="md:col-7">
                                        <div class="text-gray-500">
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
                                    <div class="md:col-5">
                                        <div class="flex content-end gap-2">
                                            @if($model->isRejected())
                                                <button
                                                    class="block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                    type="button" data-modal-toggle="discard-modal">
                                                    {{ __('ringlesoft::approvals.discard') }}
                                                </button>
                                            @else
                                                <button
                                                    class="block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                    type="button" data-modal-toggle="reject-modal">
                                                    {{ __('ringlesoft::approvals.reject') }}
                                                </button>
                                            @endif
                                            @if(!$model->isRejected())
                                                <button
                                                    class="block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                    type="button" data-modal-toggle="return-modal">
                                                    {{ __('ringlesoft::approvals.return') }}
                                                </button>
                                            @endif
                                            <button
                                                class="block text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                                type="button" data-modal-toggle="approve-modal">
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
                <div class="flex justify-between w-full mt-5">
                    <div class="md:col-7">
                        <div class="text-gray-500">
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
                    <div class="md:col-5">
                        <div class="w-100 d-flex justify-content-end gap-2 align-items-md-center">
                            @if($model)
                                <form action="{{route('ringlesoft.process-approval.submit', $model)}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    @if($model->canBeSubmittedBy(\Illuminate\Support\Facades\Auth::user()))
                                        <button
                                            class="block text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                            type="submit">
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

    <!-- Approve Modal -->
    @if($model->canBeApprovedBy(auth()?->user()))
        <div class="max-w-2xl mx-auto">
            <div id="approve-modal" aria-hidden="true"
                 class="hidden overflow-x-hidden overflow-y-auto fixed h-modal md:h-full top-4 left-0 right-0 md:inset-0 z-50 justify-center items-center">

                <div class="relative w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 pt-2">
                        <button type="button"
                                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white z-10"
                                data-modal-toggle="approve-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">{{ __('ringlesoft::approvals.close_modal') }}</span>
                        </button>
                        <div class="p-6 text-center">
                            <form method="post" class="modal-content"
                                  action="{{route('ringlesoft.process-approval.approve', $model)}}">
                                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('ringlesoft::approvals.approve_request') }}</h3>
                                <div>
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    <div class="modal-body">
                                        <div class="form-group">
                                    <textarea name="comment" id="approveComment" rows="3"
                                              placeholder="{{ __('ringlesoft::approvals.write_comment') }} ({{ __('ringlesoft::approvals.optional') }})"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button data-modal-close="approve-modal" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                                            data-modal-toggle="approve-modal"
                                    >
                                        {{ __('ringlesoft::approvals.no_cancel') }}
                                    </button>
                                    <button data-modal-hide="approve-modal" type="submit"
                                            class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                        {{ucfirst(__('ringlesoft::approvals.'. strtolower($nextApprovalStep->action ?? 'Approve') ?? __('ringlesoft::approvals.approve')))}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="max-w-2xl mx-auto">
            <div id="reject-modal" aria-hidden="true"
                 class="hidden overflow-x-hidden overflow-y-auto fixed h-modal md:h-full top-4 left-0 right-0 md:inset-0 z-50 justify-center items-center">

                <div class="relative w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button type="button"
                                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white z-10"
                                data-modal-toggle="reject-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">{{ __('ringlesoft::approvals.close_modal') }}</span>
                        </button>
                        <div class="p-6 text-center">
                            <form method="post" class="modal-content"
                                  action="{{route('ringlesoft.process-approval.reject', $model)}}">
                                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('ringlesoft::approvals.reject_request') }}</h3>
                                <div>
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    <div class="modal-body">
                                        <div class="form-group">
                                    <textarea name="comment" id="rejectComment" rows="3"
                                              placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                              required
                                    ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button data-modal-close="reject-modal" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                                            data-modal-toggle="reject-modal"
                                    >
                                        {{ __('ringlesoft::approvals.no_cancel') }}
                                    </button>
                                    <button data-modal-hide="reject-modal" type="submit"
                                            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                        {{ __('ringlesoft::approvals.reject') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Reject Modal -->
        <div class="max-w-2xl mx-auto">
            <div id="return-modal" aria-hidden="true"
                 class="hidden overflow-x-hidden overflow-y-auto fixed h-modal md:h-full top-4 left-0 right-0 md:inset-0 z-50 justify-center items-center">

                <div class="relative w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button type="button"
                                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white z-10"
                                data-modal-toggle="return-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">{{ __('ringlesoft::approvals.close_modal') }}</span>
                        </button>
                        <div class="p-6 text-center">
                            <form method="post" class="modal-content"
                                  action="{{route('ringlesoft.process-approval.return', $model)}}">
                                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('ringlesoft::approvals.return_request') }}</h3>
                                <div>
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    <div class="modal-body">
                                        <div class="form-group">
                                    <textarea name="comment" id="returnComment" rows="3"
                                              placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                              required
                                    ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button data-modal-close="return-modal" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                                            data-modal-toggle="return-modal"
                                    >
                                        {{ __('ringlesoft::approvals.no_cancel') }}
                                    </button>
                                    <button data-modal-hide="return-modal" type="submit"
                                            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                        {{ __('ringlesoft::approvals.return') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Discard Modal -->
        <div class="max-w-2xl mx-auto">
            <div id="discard-modal" aria-hidden="true"
                 class="hidden overflow-x-hidden overflow-y-auto fixed h-modal md:h-full top-4 left-0 right-0 md:inset-0 z-50 justify-center items-center">

                <div class="relative w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button type="button"
                                class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white z-10"
                                data-modal-toggle="discard-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                      stroke-width="2"
                                      d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">{{ __('ringlesoft::approvals.close_modal') }}</span>
                        </button>
                        <div class="p-6 text-center">
                            <form method="post" class="modal-content"
                                  action="{{route('ringlesoft.process-approval.discard', $model)}}">
                                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">{{ __('ringlesoft::approvals.discard_request') }}</h3>
                                <div>
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    <div class="modal-body">
                                        <div class="form-group">
                                    <textarea name="comment"
                                              required
                                              id="discardComment" rows="3"
                                              placeholder="{{ __('ringlesoft::approvals.write_comment') }}"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button data-modal-close="discard-modal" type="button"
                                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                                            data-modal-toggle="discard-modal"
                                    >
                                        {{ __('ringlesoft::approvals.no_cancel') }}
                                    </button>
                                    <button data-modal-hide="discard-modal" type="submit"
                                            class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                        {{ __('ringlesoft::approvals.discard') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
