$(document).ready(function() {
    var chatState = {
        budget: { active: false, step: 0, data: {} }
    };

    // Chatbot toggle with ARIA and focus management
    $('#chatbotToggle').on('click', function() {
        var $toggle = $(this);
        $('#chatbotWindow').slideToggle(200, function() {
            var isOpen = $(this).is(':visible');
            $toggle.attr('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) {
                setTimeout(function(){ $('#chatbotInput').trigger('focus'); }, 10);
            }
        });
    });

    // Close chatbot (button)
    $('.close-chatbot').on('click', function() {
        $('#chatbotWindow').slideUp(200, function(){
            $('#chatbotToggle').attr('aria-expanded', 'false').trigger('focus');
        });
    });

    // Close on Escape
    $(document).on('keydown', function(e){
        if (e.key === 'Escape' || e.keyCode === 27) {
            if ($('#chatbotWindow').is(':visible')) {
                $('#chatbotWindow').slideUp(200, function(){
                    $('#chatbotToggle').attr('aria-expanded', 'false').trigger('focus');
                });
            }
        }
    });

    // Actions bar
    $('#chatbotPlus').on('click', function(){
        showOptions([
            { label: 'Tell me about your services', value: 'Tell me about your services' },
            { label: 'I want a budget estimate', value: 'I want a budget estimate' },
            { label: 'Schedule a consultation', value: 'Schedule a consultation' }
        ]);
    });
    $('#chatbotVoice').on('click', function(){
        addMessage('Voice input is not enabled yet on this device. Please type your message.', 'bot');
    });

    // Send message
    $('#sendMessage').click(sendMessage);
    $('#chatbotInput').keypress(function(e) {
        if (e.which == 13) {
            sendMessage();
        }
    });
    
    function addMessage(message, sender) {
        var messageClass = sender === 'user' ? 'user-message' : 'bot-message';
        var messageHtml = '<div class="message ' + messageClass + '"><div class="message-content">' + message + '</div></div>';
        $('#chatbotMessages').append(messageHtml);
        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
    }

    function showTyping(show) {
        var id = '#typingIndicator';
        if (show) {
            if ($(id).length) return;
            var html = '<div class="message bot-message" id="typingIndicator"><div class="message-content">Typing<span class="dot">.</span><span class="dot">.</span><span class="dot">.</span></div></div>';
            $('#chatbotMessages').append(html);
            $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
        } else {
            $(id).remove();
        }
    }

    function showOptions(options) {
        var optionsHtml = '<div class="message bot-message"><div class="message-content options-container">';
        for (var i = 0; i < options.length; i++) {
            optionsHtml += '<button class="option-btn" data-option="' + options[i].value + '">' + options[i].label + '</button>';
        }
        optionsHtml += '</div></div>';
        $('#chatbotMessages').append(optionsHtml);
        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
        $('.option-btn').off('click').on('click', function() {
            var option = $(this).data('option');
            $('#chatbotInput').val(option);
            sendMessage();
        });
    }

    function renderEnquiryForm() {
        var html = ''+
        '<div class="message bot-message"><div class="message-content">'+
            '<div class="chat-enquiry">'+
                '<div class="chat-enquiry-title"><strong>Quick Enquiry</strong></div>'+
                '<form class="chat-enquiry-form">'+
                    '<div class="row"><input type="text" name="name" placeholder="Your name" required></div>'+
                    '<div class="row"><input type="email" name="email" placeholder="Email address" required></div>'+
                    '<div class="row"><input type="tel" name="phone" placeholder="Phone (optional)"></div>'+
                    '<div class="row"><textarea name="message" rows="3" placeholder="Tell us briefly about your project"></textarea></div>'+
                    '<input type="hidden" name="project_type" value="">'+
                    '<input type="hidden" name="referral" value="chatbot">'+
                    '<input type="hidden" name="newsletter" value="0">'+
                    '<button type="submit" class="btn btn-primary">Submit Enquiry</button>'+
                '</form>'+
            '</div>'+
        '</div></div>';
        $('#chatbotMessages').append(html);
        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);

        $('.chat-enquiry-form').off('submit').on('submit', function(e){
            e.preventDefault();
            var form = this;
            var btn = $(form).find('button[type="submit"]');
            var original = btn.html();
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

            var fd = new FormData(form);
            fetch('api/enquiry.php', { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    if (data && data.success) {
                        addMessage('Thank you! Your enquiry has been submitted. We\'ll contact you shortly.', 'bot');
                        // Offer to open full contact page
                        var link = '<div class="message bot-message"><div class="message-content"><a href="pages/contact.php?success=1" class="btn btn-primary" target="_self">Open Contact Page</a></div></div>';
                        $('#chatbotMessages').append(link);
                        $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);
                    } else {
                        addMessage((data && data.message) ? data.message : 'Unable to submit right now. Please try again.', 'bot');
                    }
                })
                .catch(function(){
                    addMessage('Network error while submitting. Please try again.', 'bot');
                })
                .finally(function(){
                    btn.prop('disabled', false).html(original);
                });
        });
    }
    
    function sendMessage() {
        var message = $('#chatbotInput').val().trim();
        if (!message) return;

        // Add user message to chat
        addMessage(message, 'user');
        // Clear input
        $('#chatbotInput').val('');

        // If budget flow is active, capture input locally
        if (chatState.budget.active) {
            return handleBudgetFlow(message);
        }

        // Detect budget intent quickly client-side
        if (/budget|estimate|cost|pricing/i.test(message)) {
            startBudgetFlow();
            return;
        }

        // Show typing indicator
        showTyping(true);

        // Send to API
        $.ajax({
            url: 'api/chatbot.php',
            type: 'POST',
            data: { message: message },
            success: function(response) {
                showTyping(false);
                try {
                    if (typeof response === 'string') response = JSON.parse(response);
                } catch (e) {
                    response = { message: "Sorry, I couldn't process that. Please try again." };
                }

                var botText = (response && response.message) ? response.message : "I couldn't find an answer. Please try again.";
                addMessage(botText, 'bot');

                if (response && response.options && response.options.length > 0) {
                    showOptions(response.options);
                }

                if (response && response.suggest_enquiry) {
                    renderEnquiryForm();
                }

                if (response && response.action === 'start_budget') {
                    startBudgetFlow();
                }
            },
            error: function() {
                showTyping(false);
                addMessage("Sorry, I'm having trouble responding right now. Please try again later.", 'bot');
            }
        });
    }

    function startBudgetFlow(){
        chatState.budget = { active: true, step: 1, data: {} };
        addMessage('Great! Let\'s estimate your interior budget. First, what type of project is it? (Residential or Commercial)', 'bot');
        showOptions([
            { label: 'Residential', value: 'Residential' },
            { label: 'Commercial', value: 'Commercial' }
        ]);
    }

    function handleBudgetFlow(message){
        var s = chatState.budget;
        if (s.step === 1){
            var type = /res/i.test(message) ? 'Residential' : (/com/i.test(message) ? 'Commercial' : null);
            if (!type){ addMessage('Please choose Residential or Commercial.', 'bot'); return; }
            s.data.type = type; s.step = 2;
            addMessage('Approximate built-up area in square feet?', 'bot');
            return;
        }
        if (s.step === 2){
            var size = parseInt(message.replace(/[^0-9]/g,''), 10);
            if (!size || size < 100){ addMessage('Please enter a valid area (e.g., 750).', 'bot'); return; }
            s.data.size = size; s.step = 3;
            addMessage('What finish level do you prefer? (Basic, Standard, Premium)', 'bot');
            showOptions([
                { label: 'Basic', value: 'Basic' },
                { label: 'Standard', value: 'Standard' },
                { label: 'Premium', value: 'Premium' }
            ]);
            return;
        }
        if (s.step === 3){
            var quality = /prem/i.test(message) ? 'Premium' : (/stand/i.test(message) ? 'Standard' : (/basic/i.test(message) ? 'Basic' : null));
            if (!quality){ addMessage('Please choose Basic, Standard, or Premium.', 'bot'); return; }
            s.data.quality = quality; s.step = 4;
            addMessage('Is this a new site or a renovation?', 'bot');
            showOptions([
                { label: 'New Build', value: 'New Build' },
                { label: 'Renovation', value: 'Renovation' }
            ]);
            return;
        }
        if (s.step === 4){
            var condition = /reno/i.test(message) ? 'Renovation' : (/new/i.test(message) ? 'New Build' : null);
            if (!condition){ addMessage('Please choose New Build or Renovation.', 'bot'); return; }
            s.data.condition = condition; s.step = 5;
            addMessage('Preferred material brands? (Standard or Premium brands)', 'bot');
            showOptions([
                { label: 'Standard Brands', value: 'Standard Brands' },
                { label: 'Premium Brands', value: 'Premium Brands' }
            ]);
            return;
        }
        if (s.step === 5){
            var brand = /prem/i.test(message) ? 'Premium' : (/stand/i.test(message) ? 'Standard' : null);
            if (!brand){ addMessage('Please choose Standard or Premium brands.', 'bot'); return; }
            s.data.brand = brand; s.step = 6;
            addMessage('Project urgency? (Flexible or Urgent)', 'bot');
            showOptions([
                { label: 'Flexible', value: 'Flexible' },
                { label: 'Urgent', value: 'Urgent' }
            ]);
            return;
        }
        if (s.step === 6){
            var urgency = /urgent/i.test(message) ? 'Urgent' : (/flex/i.test(message) ? 'Flexible' : null);
            if (!urgency){ addMessage('Please choose Flexible or Urgent.', 'bot'); return; }
            s.data.urgency = urgency; s.step = 7;
            addMessage('Any additional scope? Select all that apply or type skip.', 'bot');
            showOptions([
                { label: 'Modular Kitchen', value: 'Kitchen' },
                { label: 'Wardrobes', value: 'Wardrobes' },
                { label: 'False Ceiling', value: 'False Ceiling' },
                { label: 'Lighting', value: 'Lighting' },
                { label: 'Skip', value: 'Skip' }
            ]);
            return;
        }
        if (s.step === 7){
            var addons = [];
            if (/kitchen/i.test(message)) addons.push('Kitchen');
            if (/ward/i.test(message)) addons.push('Wardrobes');
            if (/ceiling/i.test(message)) addons.push('False Ceiling');
            if (/light/i.test(message)) addons.push('Lighting');
            s.data.addons = addons;

            // Compute estimate with realistic range
            var baseRates = {
                Residential: { Basic: 1200, Standard: 1700, Premium: 2300 },
                Commercial:  { Basic: 1000, Standard: 1500, Premium: 2000 }
            };
            var perSq = baseRates[s.data.type][s.data.quality];
            // condition multiplier
            var conditionMult = (s.data.condition === 'Renovation') ? 1.10 : 1.00; // +10% for renovation complexity
            // brand multiplier
            var brandMult = (s.data.brand === 'Premium') ? 1.08 : 1.00; // +8% premium brands
            // urgency multiplier
            var urgencyMult = (s.data.urgency === 'Urgent') ? 1.05 : 1.00; // +5% fast-track
            var perSqEffective = Math.round(perSq * conditionMult * brandMult * urgencyMult);

            var base = perSqEffective * s.data.size;
            var extras = 0;
            if (addons.includes('Kitchen')) extras += 150000;
            if (addons.includes('Wardrobes')) extras += 80000;
            if (addons.includes('False Ceiling')) extras += 90000;
            if (addons.includes('Lighting')) extras += 60000;
            var subtotal = base + extras;
            var gst = subtotal * 0.18; // indicative tax
            var total = Math.round(subtotal + gst);

            // Range: -7% to +7% variability
            var low = Math.round(total * 0.93);
            var high = Math.round(total * 1.07);

            function inr(n){ return '₹' + n.toLocaleString('en-IN'); }

            var summary = '<strong>Estimated Budget</strong><br>'+
                'Type: '+s.data.type+'<br>'+
                'Area: '+s.data.size+' sq.ft<br>'+
                'Finish: '+s.data.quality+'<br>'+
                'Condition: '+s.data.condition+'<br>'+
                'Brands: '+s.data.brand+'<br>'+
                'Urgency: '+s.data.urgency+'<br>'+
                (addons.length ? ('Addons: '+addons.join(', ')+'<br>') : '')+
                'Estimated range (incl. taxes): <strong>'+ inr(low) +' – '+ inr(high) +'</strong><br>'+
                '<small>Note: This is indicative and may vary with site conditions and selections.</small>';
            addMessage(summary, 'bot');

            // Redirect button to Contact page instead of inline form
            var contactHtml = '<div class="message bot-message"><div class="message-content">'+
                '<a href="pages/contact.php" class="btn btn-primary" target="_self">Continue to Contact</a>'+
                '</div></div>';
            $('#chatbotMessages').append(contactHtml);
            $('#chatbotMessages').scrollTop($('#chatbotMessages')[0].scrollHeight);

            // reset
            chatState.budget = { active: false, step: 0, data: {} };
            return;
        }
    }
    
    // Welcome message
    setTimeout(function() {
        addMessage("Hello! I'm the Living 360 Assistant. How can I help you today? You can ask me about our services, request a budget estimate, or schedule a consultation.", 'bot');
        
        // Show initial options
        showOptions([
            { label: "Tell me about your services", value: "Tell me about your services" },
            { label: "I want a budget estimate", value: "I want a budget estimate" },
            { label: "Schedule a consultation", value: "Schedule a consultation" }
        ]);
    }, 500);
});