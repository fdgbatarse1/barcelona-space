@props(['active'])

@php
    $classes = ($active ?? false)
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-gray-300 uppercase font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-500 focus:outline-none focus:border-gray-500 transition duration-150 ease-in-out'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent uppercase font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-500 focus:outline-none focus:text-gray-700 focus:border-gray-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>