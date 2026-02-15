@extends('layouts.app')

@section('title', 'Project - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
@endsection

@section('content')
    <x-pages.under-construction 
        title="Project" 
        message="This page is currently under maintenance. Please check back later." 
    />
@endsection
