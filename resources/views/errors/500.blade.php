{{-- Standalone: the app itself may be what broke. See errors/minimal. --}}
@include('errors.minimal', [
    'code'    => '500',
    'heading' => 'Something went wrong at our end',
    'body'    => 'Not you — us. The problem has been logged and we are looking at it. Try again in a moment.',
])
