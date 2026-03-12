@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full border border-orange-300/30 bg-orange-300/10 px-4 py-2 text-sm font-medium leading-5 text-orange-100 transition duration-150 ease-in-out'
            : 'inline-flex items-center rounded-full border border-transparent px-4 py-2 text-sm font-medium leading-5 text-stone-400 transition duration-150 ease-in-out hover:border-white/10 hover:bg-white/5 hover:text-stone-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
