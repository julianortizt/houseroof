@props(['currentStep' => 1])

@php
    $steps = app()->getLocale() === 'es'
        ? [
            ['title' => 'Describe tu proyecto', 'detail' => 'Comparte el alcance y la ubicación'],
            ['title' => 'Revisión del equipo', 'detail' => 'Confirmaremos los datos necesarios'],
            ['title' => 'Propuesta y siguientes pasos', 'detail' => 'El equipo se pondrá en contacto'],
        ]
        : [
            ['title' => 'Describe your project', 'detail' => 'Share the scope and location'],
            ['title' => 'Team review', 'detail' => 'We will confirm any missing details'],
            ['title' => 'Proposal and next steps', 'detail' => 'The team will follow up with you'],
        ];
@endphp

<ol class="grid gap-4 md:grid-cols-3" aria-label="{{ app()->getLocale() === 'es' ? 'Pasos de la solicitud' : 'Request steps' }}">
    @foreach($steps as $index => $step)
        @php($stepNumber = $index + 1)
        <li @if($stepNumber === $currentStep) aria-current="step" @endif
            class="rounded-xl border p-5 {{ $stepNumber === $currentStep ? 'border-amber-500 bg-white shadow-md' : 'border-gray-200 bg-white/70' }}">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $stepNumber === $currentStep ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ str_pad((string) $stepNumber, 2, '0', STR_PAD_LEFT) }}
                </span>
                <span class="font-semibold text-gray-900">{{ $step['title'] }}</span>
            </div>
            <p class="mt-3 text-sm leading-6 text-gray-600">{{ $step['detail'] }}</p>
        </li>
    @endforeach
</ol>
