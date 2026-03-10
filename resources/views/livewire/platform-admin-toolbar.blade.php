<div
    style="position: sticky; top: 0; z-index: 9999; height: 48px; background: #18181b; color: #e4e4e7; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; font-size: 13px; font-family: system-ui, -apple-system, sans-serif; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"
>
    {{-- Left: Platform label --}}
    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; letter-spacing: 0.5px;">
        <span style="color: #a78bfa;">&#9670;</span>
        Gus Platform
    </div>

    {{-- Right: Studio switcher --}}
    <div style="display: flex; align-items: center; gap: 12px;">
        <span style="color: #a1a1aa; font-size: 12px;">Studio:</span>
        <select
            wire:change="switchStudio($event.target.value)"
            style="background: #27272a; color: #e4e4e7; border: 1px solid #3f3f46; border-radius: 6px; padding: 4px 28px 4px 10px; font-size: 13px; cursor: pointer; outline: none; appearance: none; -webkit-appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;12&quot; height=&quot;12&quot; viewBox=&quot;0 0 12 12&quot;><path fill=&quot;%23a1a1aa&quot; d=&quot;M6 8L1 3h10z&quot;/></svg>'); background-repeat: no-repeat; background-position: right 8px center;"
        >
            @foreach($studios as $studio)
                <option
                    value="{{ $studio->slug }}"
                    @selected($studio->slug === $currentStudioSlug)
                >
                    {{ $studio->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
