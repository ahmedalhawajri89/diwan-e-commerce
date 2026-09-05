{{-- Standalone: shown during maintenance, when the app may be half-up. --}}
@include('errors.minimal', [
    'code'    => '503',
    'heading' => 'Back shortly',
    'body'    => 'The shop is down for a short piece of maintenance. Nothing is lost — your bag and your rooms will be where you left them.',
])
