{{--
  A missing page offers the two things a lost visitor actually wants — search,
  and the way back. The layout is in components/error-page.
--}}
@extends('layouts.furnish')

@section('title', __('diwan.error.404_title'))

@section('content')
    <x-error-page code="404" :search="true" />
@endsection
