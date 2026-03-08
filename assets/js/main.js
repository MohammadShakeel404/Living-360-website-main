// Form validation function
function validateForm(form) {
    var isValid = true;
    var requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(function(field) {
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

$(document).ready(function() {
    
    // ========================================
    // HAMBURGER MENU TOGGLE
    // ========================================
    var $hamburger = $('#hamburgerBtn');
    var $mobileDrawer = $('#mobileNavDrawer');
    
    if ($hamburger.length) {
        $hamburger.on('click', function() {
            $(this).toggleClass('active');
            $mobileDrawer.toggleClass('open');
            $('body').toggleClass('nav-open');
            $(this).attr('aria-expanded', $mobileDrawer.hasClass('open'));
        });
        
        // Close drawer when clicking a link
        $mobileDrawer.find('a').on('click', function() {
            $hamburger.removeClass('active');
            $mobileDrawer.removeClass('open');
            $('body').removeClass('nav-open');
        });
    }

    // ========================================
    // OFFER MODAL
    // ========================================
    if ($('.offer-modal').length > 0) {
        setTimeout(function() {
            $('.offer-modal').fadeIn();
        }, 2000);
        
        $('.close-modal').click(function() {
            $(this).closest('.modal').fadeOut();
        });
    }

    // ========================================
    // FORM VALIDATION ON SUBMIT
    // ========================================
    $('form').on('submit', function(e) {
        var isValid = true;
        $(this).find('[required]').each(function() {
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
    $('input, textarea, select').on('input change', function() {
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
        $('.next-step').click(function() {
            var currentStepElement = $steps.eq(currentStep - 1);
            var requiredFields = currentStepElement.find('[required]');
            var isValid = true;

            requiredFields.each(function() {
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
        $('.prev-step').click(function() {
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
        $('#formContent').hide();
        $('.enquiry-form').hide();
        setTimeout(function() {
            $('#successMessage').css('opacity', '1');
        }, 100);
    }

    // ========================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ========================================
    if ('IntersectionObserver' in window) {
        var animObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    animObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
            animObserver.observe(el);
        });
    } else {
        // Fallback: just show everything
        document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
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
        var counterObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.stat-number[data-count]').forEach(function(el) {
            counterObserver.observe(el);
        });
    }

    // ========================================
    // HEADER SCROLL EFFECT
    // ========================================
    var lastScroll = 0;
    $(window).on('scroll', function() {
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