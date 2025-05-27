@props(['title' => null])

<div {{ $attributes->merge(['class' => 'bg-white p-6 rounded-lg shadow']) }}>
    @if ($title)
        <h2 class="text-xl font-semibold mb-4">{{ $title }}</h2>
    @endif

    {{ $slot }}
</div>
