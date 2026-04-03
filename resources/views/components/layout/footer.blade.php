@php
    $legalSettings = \App\Models\IntegrationSetting::getSettings('legal');
@endphp

<footer class="footer">
    <div class="footer-content">
        <div class="footer-left">
            <p>&copy; {{ date('Y') }} Monitoring System - Pejabat Residen Sibu</p>
        </div>
        <div class="footer-right">
            <a href="#" class="footer-link" onclick="openLegalModal('disclaimer'); return false;">Disclaimer</a>
            <span class="footer-separator">|</span>
            <a href="#" class="footer-link" onclick="openLegalModal('privacy'); return false;">Privacy</a>
            <span class="footer-separator">|</span>
            <a href="#" class="footer-link" onclick="openLegalModal('terms'); return false;">Terms of Service</a>
        </div>
    </div>
</footer>

<!-- Legal Modal -->
<div id="legalModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px; max-height: 80vh; overflow: hidden;">
        <div class="modal-header">
            <h3 id="legalModalTitle" style="margin: 0; font-size: 16px; font-weight: 600; color: #333;"></h3>
            <button type="button" class="modal-close" onclick="closeLegalModal()">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y: auto; max-height: calc(80vh - 120px); padding: 24px;">
            <div id="legalModalContent" class="legal-content"></div>
            <div id="legalModalEmpty" style="display: none; text-align: center; padding: 40px 20px; color: #999;">
                <span class="material-symbols-outlined" style="font-size: 48px; color: #ddd; display: block; margin-bottom: 16px;">description</span>
                <p style="font-size: 13px; margin: 0;">No content available.</p>
                <p style="font-size: 11px; margin: 8px 0 0 0;">Please contact administrator to add content.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeLegalModal()">Close</button>
        </div>
    </div>
</div>

<script>
// Legal content from database
const legalContent = {
    disclaimer: {
        title: 'Disclaimer',
        content: @json($legalSettings['disclaimer'] ?? '')
    },
    privacy: {
        title: 'Privacy Policy',
        content: @json($legalSettings['privacy'] ?? '')
    },
    terms: {
        title: 'Terms of Service',
        content: @json($legalSettings['terms'] ?? '')
    }
};

function openLegalModal(type) {
    const modal = document.getElementById('legalModal');
    const title = document.getElementById('legalModalTitle');
    const content = document.getElementById('legalModalContent');
    const emptyState = document.getElementById('legalModalEmpty');
    
    if (legalContent[type]) {
        title.textContent = legalContent[type].title;
        
        // Check if content is empty
        if (!legalContent[type].content || legalContent[type].content.trim() === '') {
            content.style.display = 'none';
            emptyState.style.display = 'block';
        } else {
            // Display HTML content (from TinyMCE editor)
            content.innerHTML = legalContent[type].content;
            content.style.display = 'block';
            emptyState.style.display = 'none';
        }
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
}

function closeLegalModal() {
    const modal = document.getElementById('legalModal');
    modal.style.display = 'none';
    document.body.style.overflow = ''; // Restore background scrolling
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('legalModal');
    if (event.target === modal) {
        closeLegalModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('legalModal');
        if (modal.style.display === 'flex') {
            closeLegalModal();
        }
    }
});
</script>

<style>
/* Legal Modal Content Styling */
.legal-content {
    font-size: 12px;
    color: #333;
    line-height: 1.8;
}

.legal-content h2 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #007bff;
}

.legal-content h3 {
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin: 24px 0 12px 0;
}

.legal-content h4 {
    font-size: 13px;
    font-weight: 600;
    color: #333;
    margin: 16px 0 8px 0;
}

.legal-content p {
    margin: 0 0 16px 0;
    text-align: justify;
}

.legal-content ul,
.legal-content ol {
    margin: 0 0 16px 0;
    padding-left: 24px;
}

.legal-content li {
    margin-bottom: 8px;
}

.legal-content strong {
    font-weight: 600;
    color: #000;
}

.legal-content em {
    font-style: italic;
    color: #666;
}

.legal-content a {
    color: #007bff;
    text-decoration: none;
}

.legal-content a:hover {
    text-decoration: underline;
}

/* Spacing for last elements */
.legal-content p:last-child,
.legal-content ul:last-child,
.legal-content ol:last-child {
    margin-bottom: 0;
}
</style>
