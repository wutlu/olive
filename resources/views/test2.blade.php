@extends('layouts.app', [
    'footer_hide' => true
])

@section('content')
    <form method="post" action="">
        @csrf
        <textarea name="test" id="test"></textarea>
        <button type="submit">Gönder</button>
    </form>

    <pre>
    @php
    print_r($match);
    @endphp
@endsection
