@extends('layouts.app')

@section('title', 'Transfer Project - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <a href="{{ route('pages.project') }}" style="color: inherit; text-decoration: none;">Project</a>
    <span class="breadcrumb-separator">›</span>
    <span>Transfer Project</span>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div>
                <h1 class="content-title">Transfer Project</h1>
                <p class="content-description">Transfer Pre-Project yang telah diluluskan ke Project dengan No Projek dari EPU/RTP System</p>
            </div>
        </div>

        @if(session('success'))
        <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <form action="{{ route('pages.project.transfer.store') }}" method="POST">
                @csrf

                <div style="margin-bottom: 20px;">
                    <label for="pre_project_id" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">
                        Pre-Project <span style="color: #dc3545;">*</span>
                    </label>
                    <select 
                        name="pre_project_id" 
                        id="pre_project_id" 
                        required
                        style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                    >
                        <option value="">-- Pilih Pre-Project --</option>
                        @foreach($preProjects as $preProject)
                            <option value="{{ $preProject->id }}" {{ old('pre_project_id') == $preProject->id ? 'selected' : '' }}>
                                {{ $preProject->name }} ({{ $preProject->parliament?->name ?? $preProject->dun?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @if($preProjects->isEmpty())
                        <p style="margin-top: 5px; font-size: 11px; color: #666;">
                            Tiada Pre-Project yang boleh ditransfer. Hanya Pre-Project dengan status "Waiting for EPU Approval" boleh ditransfer.
                        </p>
                    @endif
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="project_number" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">
                        No Projek <span style="color: #dc3545;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="project_number" 
                        id="project_number" 
                        value="{{ old('project_number') }}"
                        required
                        placeholder="Contoh: PROJ/2026/001"
                        style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                    >
                    <p style="margin-top: 5px; font-size: 11px; color: #666;">
                        Masukkan No Projek yang telah diluluskan oleh EPU dari RTP System
                    </p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="project_year" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 12px;">
                        Tahun <span style="color: #dc3545;">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="project_year" 
                        id="project_year" 
                        value="{{ old('project_year', date('Y')) }}"
                        required
                        placeholder="Contoh: 2026"
                        maxlength="4"
                        style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                    >
                    <p style="margin-top: 5px; font-size: 11px; color: #666;">
                        Masukkan tahun projek (4 digit)
                    </p>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button 
                        type="submit" 
                        class="btn-primary"
                        style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"
                        {{ $preProjects->isEmpty() ? 'disabled' : '' }}
                    >
                        Transfer
                    </button>
                    <a 
                        href="{{ route('pages.project') }}" 
                        class="btn-secondary"
                        style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;"
                    >
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
