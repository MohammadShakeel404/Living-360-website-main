// Form validation function
function validateForm(form) {
    var isValid = true;
    var requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function (field) {
        if (!field.value || !field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
        // Email validation
        if (field.type === 'email' && field.value) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                field.classList.add('error');
                isValid = false;
            }
        }
    });
    return isValid;
}

$(document).ready(function () {

    // ========================================
    // HAMBURGER MENU TOGGLE
    // ========================================
    var $hamburger = $('#hamburgerBtn');
    var $mobileDrawer = $('#mobileNavDrawer');

    if ($hamburger.length) {
        $hamburger.on('click', function () {
            $(this).toggleClass('active');
            $mobileDrawer.toggleClass('open');
            $('body').toggleClass('nav-open');
            $(this).attr('aria-expanded', $mobileDrawer.hasClass('open'));
        });

        // Close drawer when clicking a link
        $mobileDrawer.find('a').on('click', function () {
            $hamburger.removeClass('active');
            $mobileDrawer.removeClass('open');
            $('body').removeClass('nav-open');
        });
    }

    // ========================================
    // OFFER MODAL (Show Once Per Visitor)
    // ========================================
    if ($('.offer-modal').length > 0) {
        // Check if the user has already seen the offer
        if (!localStorage.getItem('living360_offer_seen')) {
            setTimeout(function () {
                $('.offer-modal').css('display', 'flex').hide().fadeIn();
            }, 2000);
        }

        // When closed, mark as seen
        $('#closeOfferModal').click(function () {
            $(this).closest('.modal').fadeOut();
            localStorage.setItem('living360_offer_seen', 'true');
        });

        // Also close on outside click
        $(window).click(function (e) {
            if ($(e.target).hasClass('offer-modal')) {
                $('#closeOfferModal').trigger('click');
            }
        });
    }

    // ========================================
    // FORM VALIDATION ON SUBMIT
    // ========================================
    $('form').on('submit', function (e) {
        var isValid = true;
        $(this).find('[required]').each(function () {
            if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('error');
                isValid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        if (!isValid) { e.preventDefault(); }
    });

    // Remove error class on input
    $('input, textarea, select').on('input change', function () {
        $(this).removeClass('error');
    });

    // ========================================
    // ENQUIRY FORM MULTI-STEP
    // ========================================
    if ($('.enquiry-form').length > 0) {
        function updateProgressBar(current, total) {
            var percent = (current / total) * 100;
            $('.progress-bar').css('width', percent + '%');
            $('.step-indicator').text('Step ' + current + ' of ' + total);
        }

        var $steps = $('.enquiry-form .form-step');
        var totalSteps = $steps.length;
        var currentStep = 1;

        // Hide all first, then show step 1
        $steps.hide();
        $steps.eq(0).show();
        updateProgressBar(currentStep, totalSteps);
        $('.prev-step').hide();
        $('.submit-form').hide();

        // Next step
        $('.next-step').click(function () {
            var currentStepElement = $steps.eq(currentStep - 1);
            var requiredFields = currentStepElement.find('[required]');
            var isValid = true;

            requiredFields.each(function () {
                if (($(this).is(':radio') || $(this).is(':checkbox'))) {
                    var name = $(this).attr('name');
                    if (!$('input[name="' + name + '"]:checked').length) {
                        isValid = false;
                    }
                } else if (!$(this).val() || !$(this).val().trim()) {
                    $(this).addClass('error');
                    isValid = false;
                }
            });

            if (isValid) {
                $steps.eq(currentStep - 1).hide();
                currentStep++;
                $steps.eq(currentStep - 1).show();
                updateProgressBar(currentStep, totalSteps);

                // Show/hide nav buttons
                if (currentStep > 1) { $('.prev-step').show(); }
                if (currentStep === totalSteps) {
                    $('.next-step').hide();
                    $('.submit-form').show();
                } else {
                    $('.prev-step').show();
                    $('.next-step').show();
                    $('.submit-form').hide();
                }
            }
        });

        // Previous step
        $('.prev-step').click(function () {
            $steps.eq(currentStep - 1).hide();
            currentStep--;
            $steps.eq(currentStep - 1).show();
            updateProgressBar(currentStep, totalSteps);

            $('.next-step').show();
            $('.submit-form').hide();
            if (currentStep === 1) { $('.prev-step').hide(); }
        });
    }

    // ========================================
    // SUCCESS MESSAGE ON PAGE LOAD
    // ========================================
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success') && urlParams.get('success') === '1') {
        $('#successMessage').show();
        // Hide the entire form container (trust text + form wrapper)
        $('.contact-form-container').hide();
        $('#formContent').hide();
        $('.enquiry-form').hide();
        setTimeout(function () {
            $('#successMessage').css('opacity', '1');
        }, 100);
    }

    // ========================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ========================================
    if ('IntersectionObserver' in window) {
        var animObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    animObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach(function (el) {
            animObserver.observe(el);
        });
    } else {
        // Fallback: just show everything
        document.querySelectorAll('.animate-on-scroll').forEach(function (el) {
            el.classList.add('animated');
        });
    }

    // ========================================
    // ANIMATED COUNTERS
    // ========================================
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target)) return;
        var duration = 2000;
        var start = 0;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var progress = Math.min((timestamp - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            var current = Math.floor(eased * target);
            el.textContent = current;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target;
            }
        }
        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-number[data-count]').forEach(function (el) {
            counterObserver.observe(el);
        });
    }

    // ========================================
    // HEADER SCROLL EFFECT
    // ========================================
    var lastScroll = 0;
    $(window).on('scroll', function () {
        var scrollTop = $(this).scrollTop();
        if (scrollTop > 100) {
            $('.main-header').addClass('scrolled');
        } else {
            $('.main-header').removeClass('scrolled');
        }
        lastScroll = scrollTop;
    });
});

// contactSubmit function for the enquiry form
function contactSubmit(form) {
    return validateForm(form);
}

// ========================================
// POPUP ENQUIRY MODAL
// ========================================
$(document).ready(function () {
    var $popupModal = $('#popupEnquiryModal');
    if (!$popupModal.length) {
        // Fallback: on contact page, redirect clicks to scroll to form
        $(document).on('click', '.open-popup-enquiry', function (e) {
            e.preventDefault();
            window.location.href = 'index.php?page=contact';
        });
        return;
    }

    var $formScreen = $('#popupFormScreen');
    var $thankyou = $('#popupThankyou');
    var $form = $('#popupEnquiryForm');
    var $steps = $form.find('.popup-step');
    var totalSteps = $steps.length;
    var currentStep = 1;

    function updatePopupProgress() {
        var pct = (currentStep / totalSteps) * 100;
        $('#popupProgressBar').css('width', pct + '%');
        $('#popupStepLabel').text('Step ' + currentStep + ' of ' + totalSteps);
    }

    function showStep(step) {
        $steps.hide();
        $steps.filter('[data-step="' + step + '"]').show();
        updatePopupProgress();
    }

    // Open popup
    $(document).on('click', '.open-popup-enquiry', function (e) {
        e.preventDefault();
        $popupModal.addClass('active');
        $('body').css('overflow', 'hidden');
        // Reset to step 1 if form is showing
        currentStep = 1;
        showStep(1);
        $formScreen.show();
        $thankyou.hide();
    });

    // Close popup
    function closePopup() {
        $popupModal.removeClass('active');
        $('body').css('overflow', '');
    }

    $('#closePopupEnquiry').on('click', closePopup);

    $popupModal.on('click', function (e) {
        if ($(e.target).hasClass('popup-enquiry-modal')) closePopup();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $popupModal.hasClass('active')) closePopup();
    });

    // Next step
    $form.on('click', '.popup-next-step', function () {
        var $currentStep = $steps.filter('[data-step="' + currentStep + '"]');
        var valid = true;

        // Validate required fields in current step
        $currentStep.find('[required]').each(function () {
            if ($(this).is(':radio') || $(this).is(':checkbox')) {
                var name = $(this).attr('name');
                if (!$form.find('input[name="' + name + '"]:checked').length) {
                    valid = false;
                }
            } else if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('error');
                valid = false;
            } else {
                $(this).removeClass('error');
            }
        });

        if (valid && currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });

    // Previous step
    $form.on('click', '.popup-prev-step', function () {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // Remove error on input
    $form.on('input change', 'input, textarea, select', function () {
        $(this).removeClass('error');
    });

    // Form submit via AJAX
    $form.on('submit', function (e) {
        e.preventDefault();

        // Validate last step
        var $lastStep = $steps.filter('[data-step="' + totalSteps + '"]');
        var valid = true;

        $lastStep.find('[required]').each(function () {
            if (!$(this).val() || !$(this).val().trim()) {
                $(this).addClass('error');
                valid = false;
            } else {
                $(this).removeClass('error');
            }

            // Email validation
            if ($(this).attr('type') === 'email' && $(this).val()) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($(this).val())) {
                    $(this).addClass('error');
                    valid = false;
                }
            }
        });

        if (!valid) return;

        var $submitBtn = $form.find('.popup-submit-btn');
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

        // Map popup field names to API field names
        var formData = {
            name: $form.find('[name="popup_name"]').val(),
            email: $form.find('[name="popup_email"]').val(),
            phone: $form.find('[name="popup_phone"]').val(),
            project_type: $form.find('[name="popup_project_type"]:checked').val() || '',
            space_size: $form.find('[name="popup_space_size"]').val() || '',
            budget: $form.find('[name="popup_budget"]:checked').val() || '',
            timeline: $form.find('[name="popup_timeline"]:checked').val() || '',
            message: $form.find('[name="popup_message"]').val() || '',
            referral: $form.find('[name="popup_referral"]').val() || '',
            newsletter: $form.find('[name="popup_newsletter"]').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: 'api/enquiry.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function (resp) {
                if (resp && resp.success) {
                    // Show thank you
                    $formScreen.hide();
                    $thankyou.show();
                    $form[0].reset();
                    currentStep = 1;
                } else {
                    alert(resp.message || 'Something went wrong. Please try again.');
                    $submitBtn.prop('disabled', false).html('Submit Enquiry <i class="fas fa-paper-plane"></i>');
                }
            },
            error: function () {
                alert('Network error. Please check your connection and try again.');
                $submitBtn.prop('disabled', false).html('Submit Enquiry <i class="fas fa-paper-plane"></i>');
            }
        });
    });

    // Submit another
    $('#popupSubmitAnother').on('click', function () {
        $thankyou.hide();
        $formScreen.show();
        currentStep = 1;
        showStep(1);
        $form.find('.popup-submit-btn').prop('disabled', false).html('Submit Enquiry <i class="fas fa-paper-plane"></i>');
    });
});