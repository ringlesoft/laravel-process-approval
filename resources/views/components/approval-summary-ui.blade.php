<span class="flex rounded" style="display:flex; margin: auto;">
    <style>

    </style>
    @foreach(($steps ?? []) as $key => $item)
        <span class="badge summary-stat"
              style="background-color: {{$theme['color'] }}; padding: .1rem; display: flex; align-items: center; justify-content: center;"
              title="{{($item['role_name'] ?? $item['role_id']) }}: {{($item['process_approval_action'] ?? 'Pending') }}"
              data-bs-toggle="tooltip">
                {!! $theme['icon']  !!} @if($showRole) &nbsp;{{strtoupper(substr($item['role_name'] ?? $item['role_id'], 0, 2))}} @endif
        </span>
    @endforeach
</span>
