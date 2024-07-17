<?php

namespace RingleSoft\LaravelProcessApproval\View\Components;


use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use RingleSoft\LaravelProcessApproval\Contracts\ApprovableModel;

class ApprovalStatusSummary extends Component
{
    public array $map;
    public $steps;
    public bool $showRole = false;
    public function __construct(public ApprovableModel $model, bool|null $showRole = true)
    {
        $check = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m2.25 12.321 7.27 6.491c.143.127.321.19.499.19.206 0 .41-.084.559-.249l11.23-12.501c.129-.143.192-.321.192-.5 0-.419-.338-.75-.749-.75-.206 0-.411.084-.559.249l-10.731 11.945-6.711-5.994c-.144-.127-.322-.19-.5-.19-.417 0-.75.336-.75.749 0 .206.084.412.25.56" fill-rule="nonzero" fill="#fff"/></svg>';
        $rejected = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12.002 21.534c5.518 0 9.998-4.48 9.998-9.998s-4.48-9.997-9.998-9.997c-5.517 0-9.997 4.479-9.997 9.997s4.48 9.998 9.997 9.998zm0-1.5c-4.69 0-8.497-3.808-8.497-8.498s3.807-8.497 8.497-8.497 8.498 3.807 8.498 8.497-3.808 8.498-8.498 8.498zm0-6.5c-.414 0-.75-.336-.75-.75v-5.5c0-.414.336-.75.75-.75s.75.336.75.75v5.5c0 .414-.336.75-.75.75zm-.002 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z" fill-rule="nonzero" fill="#fff"/></svg>';
        $discarded = '<svg clip-rule="evenodd" fill-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" width="12" height="12" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="m12 10.93 5.719-5.72c.146-.146.339-.219.531-.219.404 0 .75.324.75.749 0 .193-.073.385-.219.532l-5.72 5.719 5.719 5.719c.147.147.22.339.22.531 0 .427-.349.75-.75.75-.192 0-.385-.073-.531-.219l-5.719-5.719-5.719 5.719c-.146.146-.339.219-.531.219-.401 0-.75-.323-.75-.75 0-.192.073-.384.22-.531l5.719-5.719-5.72-5.719c-.146-.147-.219-.339-.219-.532 0-.425.346-.749.75-.749.192 0 .385.073.531.219z" fill="#fff"/></svg>';
        $pending = '<svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" width="12" height="12" viewBox="0 0 24 24"><path d="M12 0c6.623 0 12 5.377 12 12s-5.377 12-12 12-12-5.377-12-12 5.377-12 12-12zm0 1c6.071 0 11 4.929 11 11s-4.929 11-11 11-11-4.929-11-11 4.929-11 11-11zm0 11h6v1h-7v-9h1v8z" fill="#fff"/></svg>';

        $this->map = [
            'Approved' => [
                'icon' => $check,
                'color' => "rgba(50, 205, 50, 0.6)"
            ],
            'Rejected' => [
                'icon' => $rejected,
                'color' => "rgba(220, 20, 60, 0.6)"
            ],
            'Returned' => [
                'icon' => $rejected,
                'color' => "rgba(220, 20, 60, 0.6)"
            ],
            'Overridden' => [
                'icon' => $rejected,
                'color' => "rgba(220, 20, 60, 0.6)"
            ],
            'Pending' => [
                'icon' => $pending,
                'color' => "rgba(255, 165, 0, 0.6)"
            ],
            'Discarded' => [
                'icon' => $discarded,
                'color' => "rgba(220, 20, 60, 0.6)"
            ],
            'Default' => [
                'icon' => $pending,
                'color' => "rgba(255, 165, 0, 0.6)"
            ],
            'Created' => [
                'icon' => $pending,
                'color' => "rgba(255, 165, 0, 0.6)"
            ]
        ];
        $this->steps = $model->approvalStatus->steps ?? [];

    }


    public function render(): View
    {
        return view()->file(__DIR__.'/../../../resources/views/components/approval-summary-ui.blade.php');
    }
}
