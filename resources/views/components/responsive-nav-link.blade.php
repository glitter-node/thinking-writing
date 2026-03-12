@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-2xl border border-orange-300/30 bg-orange-300/10 px-4 py-3 text-start text-base font-medium text-orange-100 transition duration-150 ease-in-out'
            : 'block w-full rounded-2xl border border-transparent px-4 py-3 text-start text-base font-medium text-stone-300 transition duration-150 ease-in-out hover:border-white/10 hover:bg-white/5 hover:text-stone-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
