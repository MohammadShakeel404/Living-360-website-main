$(document).ready(function() {
    // Delete confirmation modal
    $('.delete-btn').click(function() {
        var id = $(this).data('id');
        $('#deleteId').val(id);
        $('#deleteModal').fadeIn();
    });
    
    $('.close-modal, .close-modal-btn').click(function() {
        $('.modal').fadeOut();
    });
    
    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            $('.modal').fadeOut();
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        // Ensure WYSIWYG editors (like TinyMCE) sync their content back to the textarea
        if (typeof tinymce !== 'undefined' && tinymce.triggerSave) {
            tinymce.triggerSave();
        }
        var requiredFields = $(this).find('[required]');
        var isValid = true;
        
        requiredFields.each(function() {
            if ($(this).val() === '') {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
    
    // Remove error class on input
    $('input, textarea, select').on('input', function() {
        $(this).removeClass('error');
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Mobile menu toggle (for responsive admin panel)
    $('.mobile-menu-toggle').click(function() {
        $('.admin-sidebar').toggleClass('active');
        $(this).toggleClass('active');
    });
});