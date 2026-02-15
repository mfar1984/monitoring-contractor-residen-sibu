@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <h1>General Settings</h1>
        <p>Configure translation settings for the application</p>
    </div>

    <x-general-tabs active="translation" />

    <div class="content-body">
        <div class="card">
            <div class="card-header">
                <h2>Translation Settings</h2>
            </div>
            <div class="card-body">
                <p>Translation settings will be configured here.</p>
            </div>
        </div>
    </div>
</div>
@endsection
