@if($model->approvalsPaused !== true)
    <div {{ $attributes->class(['flex w-full justify-center p-3']) }} >
        <div class="card-body w-full p-3 overflow-x-auto  sm:rounded-lg border border-gray-300 ">
            <h6 class="text-center">Approvals</h6>
            @if($model->isSubmitted())
                <div class="approvals relative mt-3">
                    <table
                        class="w-full text-sm text-left text-gray-800 dark:text-gray-400 mb-2 border border-collapse">
                        <thead>
                        <tr>
                            <th class="border p-2" style="width: 50px;">By:</th>
                            @foreach($modelApprovalSteps as $item)
                                <th style="width: {{(100 / count($modelApprovalSteps))}}%;"
                                    class="text-center border">{{$item['step']->role?->name ?? 'Step '. $loop->iteration}}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <th class="p-2">Date</th>
                            @foreach($modelApprovalSteps as $item)
                                <td class="border p-2">
                                    <div class="text-center">
                                        @if($currentApproval = $item['approval'])
                                            <div class="relative group">
                                                <div
                                                    class="absolute bottom-0 left-1/2 -translate-x-1/2 transform opacity-0 scale-75 origin-bottom group-hover:opacity-100 transition-all duration-300 z-10 px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                                    {{$currentApproval->comment ?? 'No comment!'}}
                                                </div>
                                                @if($currentApproval->approval_action === \RingleSoft\LaravelProcessApproval\Enums\ApprovalActionEnum::APPROVED->value)
                                                    <div class="relative group">
                                                        <div
                                                            class="absolute bottom-0 left-1/2 -translate-x-1/2 transform opacity-0 scale-75 origin-bottom group-hover:opacity-100 transition-all duration-300 z-10 px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
                                                            {{$currentApproval->comment ?? 'No comment!'}}
                                                        </div>
                                                        @if($signature = $currentApproval->getSignature())
                                                            <div class="flex justify-center">
                                                                <img src="{{$signature}}" class="img-fluid"
                                                                     style="max-height: 50px;" alt="Signature">
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
                                                             class="rounded bg-red-400 flex justify-center items-center h-full">
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
                                <div class="col-12 md:col-7">
                                    <div class="text-gray-500">
                                        @if($model->isRejected())
                                            This request was rejected. You can re-approve this as
                                            <strong>{{$nextApprovalStep->role->name}}</strong>
                                        @else
                                            You Can approve this as <strong>{{$nextApprovalStep->role->name}}</strong>
                                        @endif

                                    </div>
                                </div>
                                <div class="col-12 md:col-5">
                                    <div class="flex content-end gap-2">
                                        @if($model->isRejected())
                                            <button
                                                class="block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                type="button" data-modal-toggle="discard-modal">
                                                Discard
                                            </button>
                                        @else
                                            <button
                                                class="block text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-800"
                                                type="button" data-modal-toggle="reject-modal">
                                                Reject
                                            </button>
                                        @endif
                                        <button
                                            class="block text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                            type="button" data-modal-toggle="approve-modal">
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
                <div class="flex justify-between w-full mt-5">
                    <div class="col-12 md:col-7">
                        <div class="text-gray-500">
                            This document is not yet submitted.
                            @if($model->creator?->id === \Illuminate\Support\Facades\Auth::id())
                                <span>
                            You can submit this document for approvals.
                        </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 md:col-5">
                        <div class="w-100 d-flex justify-content-end gap-2 align-items-md-center">
                            @if($model)
                                <form action="{{route('ringlesoft.process-approval.submit', $model)}}" method="post">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                    <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                    @if($model->creator?->id === \Illuminate\Support\Facades\Auth::id())
                                        <button
                                            class="block text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"
                                            type="submit">
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

    <!-- Approve Modal -->
    <div class="max-w-2xl mx-auto">
        <div id="approve-modal" aria-hidden="true"
             class="hidden overflow-x-hidden overflow-y-auto fixed h-modal md:h-full top-4 left-0 right-0 md:inset-0 z-50 justify-center items-center">

            <div class="relative w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                    <button type="button"
                            class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="approve-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-6 text-center">
                        <form method="post" class="modal-content"
                              action="{{route('ringlesoft.process-approval.approve', $model)}}">
                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Approve Request</h3>
                            <div>
                                @csrf
                                <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                <div class="modal-body">
                                    <div class="form-group">
                                    <textarea name="comment" id="approveComment" rows="3"
                                              placeholder="Write a comment (optional)"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button data-modal-close="approve-modal" type="button"
                                        class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                    No, cancel
                                </button>
                                <button data-modal-hide="approve-modal" type="submit"
                                        class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 dark:focus:ring-green-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                    {{ucfirst($nextApprovalStep->action ?? 'Approve')}}
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
                            class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="reject-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-6 text-center">
                        <form method="post" class="modal-content"
                              action="{{route('ringlesoft.process-approval.reject', $model)}}">
                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Reject Request</h3>
                            <div>
                                @csrf
                                <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                <div class="modal-body">
                                    <div class="form-group">
                                    <textarea name="comment" id="rejectComment" rows="3"
                                              placeholder="Write your approval comment"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button data-modal-close="reject-modal" type="button"
                                        class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                    No, cancel
                                </button>
                                <button data-modal-hide="reject-modal" type="submit"
                                        class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                    Reject
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
                            class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-toggle="discard-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                    <div class="p-6 text-center">
                        <form method="post" class="modal-content"
                              action="{{route('ringlesoft.process-approval.discard', $model)}}">
                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Discard Request</h3>
                            <div>
                                @csrf
                                <input type="hidden" name="user_id" value="{{auth()?->id()}}">
                                <input type="hidden" name="model_name" value="{{$model->getApprovableType()}}">
                                <div class="modal-body">
                                    <div class="form-group">
                                    <textarea name="comment" id="discardComment" rows="3"
                                              placeholder="Write your approval comment"
                                              class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button data-modal-close="discard-modal" type="button"
                                        class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                    No, cancel
                                </button>
                                <button data-modal-hide="discard-modal" type="submit"
                                        class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                    Discard
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Trigger element -->
    <button class="relative group">
        Hover for Tooltip

        <!-- Tooltip -->
        <div
            class="absolute bottom-0 left-1/2 -translate-x-1/2 transform opacity-0 scale-75 origin-bottom group-hover:opacity-100 transition-all duration-300 z-10 px-3 py-2 text-sm text-white bg-gray-800 rounded-lg shadow-lg">
            This is a tooltip message
        </div>
    </button>

@endif
