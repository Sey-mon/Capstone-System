<!-- Error Content -->
<div class="ajax-error-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1rem; padding: 3rem 2rem; text-align: center;">
    <div style="font-size: 3rem; color: #ef4444;">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <h3 style="margin: 0; color: #1f2937; font-size: 1.25rem; font-weight: 600;">Error Loading Content</h3>
    <p style="margin: 0; color: #6b7280; max-width: 400px; line-height: 1.5;">
        {{ $error ?? 'An error occurred while loading the requested information. Please try again later.' }}
    </p>
    <button onclick="closeAjaxModal()" style="padding: 0.75rem 1.5rem; background: #ef4444; color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; margin-top: 1rem;">
        Close
    </button>
</div>
