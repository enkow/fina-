<x-mail::message :markdown="$markdown??true">
{{-- Greeting --}}
@if($markdown??true)
    @if (! empty($greeting))
        # {{ $greeting }}
    @else
        @if ($level === 'error')
            # @lang('Whoops!')
        @else
            # {{ ucfirst(__('main.hello')) }}!
        @endif
    @endif
@else
    @if (! empty($greeting))
        <b style="font-size:15px !important;">{!! $greeting !!}</b>
        @if(!empty($afterGreeting))
            <br>{!! $afterGreeting !!}
        @endif
    @else
        @if ($level === 'error')
            <b style="font-size:15px !important;">@lang('Whoops!')</b>
        @else
            <b style="font-size:15px !important;">{{ ucfirst(__('main.hello')) }}!</b>
        @endif
    @endif
@endif

@if(isset($reservationData))
    @include('mails.partials.reservation_table',['reservationData' => $reservationData])
@endif


{{-- Intro Lines --}}
@foreach ($introLines as $line)
{!! $line !!}<br>
@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php $color = match ($level) {
	'success', 'error' => $level,
	default => 'primary',
}; ?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{!! $line !!}

@endforeach

@if(!($markdown??true))
    <p style="width:100%;text-align:center; font-size: 10px; opacity: 0.7;margin-top:20px;margin-bottom:0 !important;">{{ __('main.mail-do-not-reply-info') }}</p>
@endif
</x-mail::message>
