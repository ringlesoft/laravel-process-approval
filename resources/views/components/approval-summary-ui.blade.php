<style>
    .summary-stats>span {
        border-radius: 0;
        padding: .2rem !important;
        cursor: pointer;
    }
    .summary-stats>span:hover {
        background-color: #f5f5f5;
    }
    .summary-stats>span:first-child {
        border-radius: 5px 0 0 5px;
    }
    .summary-stats>span:last-child {
        border-radius: 0 5px 5px 0;
    }
</style>
<span class="flex rounded summary-stats" style="display:flex; margin: auto;">

    @foreach(($steps ?? []) as $key => $item)
        @php
            $theme = $map[$item['process_approval_action'] ?? 'Default'];
        @endphp
        <span class="badge summary-stat"
              style="background-color: {{$theme['color'] }}; display: flex; align-items: center; justify-content: center;"
              title="{{($item['role_name'] ?? $item['role_id']) }}: {{($item['process_approval_action'] ?? 'Pending') }}"
              data-bs-toggle="tooltip" data-toggle="tooltip">
                {!! $theme['icon']  !!} @if($showRole) &nbsp;{{strtoupper(substr($item['role_name'] ?? $item['role_id'], 0, 2))}} @endif
        </span>
    @endforeach
</span>
