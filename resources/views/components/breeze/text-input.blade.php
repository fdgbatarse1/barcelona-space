@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border border-gray-400 focus:border-gray-500 focus:ring-gray-500 rounded-md shadow-xs px-2 py-1    ']) }}>