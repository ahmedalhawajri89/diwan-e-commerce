{{--
  The app is healthy here — the visitor simply cannot have this page — so it
  keeps the storefront around them rather than dropping them onto a bare screen.
  500 and 503 are the opposite case and stand alone; see errors/minimal.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.error.429_title'))

@section('content')
    <x-error-page code="429" />
@endsection
