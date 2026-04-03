@extends('layouts.app')

@section('title', 'General Settings - Legal - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Legal</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="legal" />
        
        <div class="tabs-content">
            <div class="content-header">
                <div class="content-header-left">
                    <h3>Legal Information</h3>
                    <p class="content-description">Manage legal documents and policies with rich text editor.</p>
                </div>
            </div>

            @if(session('success'))
            <div style="padding: 12px 16px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 20px; font-size: 12px;">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('pages.general.legal.store') }}" id="legalForm">
                @csrf

                <!-- Disclaimer Section -->
                <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Disclaimer</h4>
                    
                    <div class="form-group">
                        <label for="disclaimer">Disclaimer Content</label>
                        <textarea 
                            id="disclaimer" 
                            name="disclaimer" 
                            class="tinymce-editor">{{ old('disclaimer', $settings['disclaimer'] ?? '') }}</textarea>
                        @error('disclaimer')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Privacy Section -->
                <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Privacy Policy</h4>
                    
                    <div class="form-group">
                        <label for="privacy">Privacy Policy Content</label>
                        <textarea 
                            id="privacy" 
                            name="privacy" 
                            class="tinymce-editor">{{ old('privacy', $settings['privacy'] ?? '') }}</textarea>
                        @error('privacy')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Terms of Service Section -->
                <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Terms of Service</h4>
                    
                    <div class="form-group">
                        <label for="terms">Terms of Service Content</label>
                        <textarea 
                            id="terms" 
                            name="terms" 
                            class="tinymce-editor">{{ old('terms', $settings['terms'] ?? '') }}</textarea>
                        @error('terms')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Save Button -->
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;">
                        Save Legal Information
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.tiny.cloud/1/{{ env('TINYMCE_API_KEY') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '.tinymce-editor',
    height: 400,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
             'alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | forecolor backcolor | ' +
             'removeformat | help',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; }',
    branding: false,
    promotion: false,
    statusbar: true,
    resize: true,
    elementpath: false,
    setup: function(editor) {
        editor.on('init', function() {
            console.log('TinyMCE editor initialized');
        });
    }
});
</script>
@endsection
