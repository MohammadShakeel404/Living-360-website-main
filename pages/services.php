<div class="page-hero">
    <div class="container">
        <span class="section-tag">What We Do</span>
        <h1>Our <span class="text-gradient">Design Services</span></h1>
        <p>Comprehensive interior design services to transform any space into something extraordinary.</p>
        <a href="index.php?page=contact" class="btn btn-cta" style="margin-top:16px;">Get a Free Quote <i
                class="fas fa-arrow-right"></i></a>
    </div>
</div>

<div class="section services-section">
    <div class="container">
        <div class="services-grid-home">
            <?php
            $services = getActiveServices();
            foreach ($services as $service) {
                $title = trim($service['title']);
                $excerpt = strip_tags($service['description']);
                $excerpt = strlen($excerpt) > 160 ? substr($excerpt, 0, 160) . '...' : $excerpt;
                $img = isset($service['image']) && $service['image'] ? 'assets/images/uploads/' . $service['image'] : 'assets/images/about-image.jpg';
                echo '<div class="service-card-home animate-on-scroll" data-service-id="' . (int) $service['id'] . '">
                        <div class="service-card-image">
                            <img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($title) . '">
                            <div class="service-card-overlay">
                                <button type="button" class="btn btn-white btn-sm view-service-details" data-service-id="' . (int) $service['id'] . '">View Details</button>
                                <a href="index.php?page=contact" class="btn btn-cta btn-sm">Get Quote</a>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <h3>' . htmlspecialchars($title) . '</h3>
                            <p>' . htmlspecialchars($excerpt) . '</p>
                        </div>
                    </div>';
            }
            ?>
        </div>

        <div class="text-center" style="margin-top:32px;">
            <a href="index.php?page=contact" class="btn btn-cta btn-lg">Discuss Your Project <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Service Modal -->
<div id="serviceModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeServiceModal">&times;</span>
        <div id="serviceModalBody"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sModal = document.getElementById('serviceModal');
        const sBody = document.getElementById('serviceModalBody');
        const sClose = document.getElementById('closeServiceModal');

        function openServiceModal(data) {
            const title = data.title ? data.title : '';
            const desc = data.description ? data.description : '';
            const img = data.image ? 'assets/images/uploads/' + data.image : '';
            sBody.innerHTML = `
      <div class="project-detail">
        <div class="project-gallery">
          <div class="main-image">${img ? `<img src="${img}" alt="${title || 'Service image'}">` : ''}</div>
        </div>
        <div class="project-description">
          <h2 class="text-gradient">${title}</h2>
          <p>${desc}</p>
          <div class="project-cta"><a href="index.php?page=contact" class="btn btn-cta">Get a Quote for This Service <i class="fas fa-arrow-right"></i></a></div>
        </div>
      </div>`;
            sModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        document.querySelectorAll('.view-service-details').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-service-id');
                if (!id) return;
                fetch('api/service.php?id=' + encodeURIComponent(id))
                    .then(r => r.json())
                    .then(res => {
                        if (res && res.success && res.data) { openServiceModal(res.data); }
                        else { sBody.innerHTML = '<p style="padding:24px;">Unable to load service details.</p>'; sModal.style.display = 'block'; }
                    })
                    .catch(() => { sBody.innerHTML = '<p style="padding:24px;">Unable to load service details.</p>'; sModal.style.display = 'block'; });
            });
        });

        function closeSModal() { sModal.style.display = 'none'; sBody.innerHTML = ''; document.body.style.overflow = ''; }
        sClose.addEventListener('click', closeSModal);
        window.addEventListener('click', (e) => { if (e.target === sModal) closeSModal(); });
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSModal(); });
    });
</script>

<!-- Design Process -->
<div class="section process-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Our Approach</span>
            <h2>Our <span class="text-gradient">Design Process</span></h2>
            <p>A systematic approach to deliver exceptional results, from conversation to final reveal.</p>
        </div>
        <div class="process-timeline">
            <div class="process-step animate-on-scroll">
                <div class="step-number">01</div>
                <div class="step-icon"><i class="fas fa-comments"></i></div>
                <h3>Consultation</h3>
                <p>We understand your goals, preferences, and budget to set the foundation.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">02</div>
                <div class="step-icon"><i class="fas fa-lightbulb"></i></div>
                <h3>Concept Development</h3>
                <p>We craft a design direction that reflects your style and functional needs.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">03</div>
                <div class="step-icon"><i class="fas fa-ruler-combined"></i></div>
                <h3>Design Planning</h3>
                <p>Detailed layouts, materials, and color palettes prepared for your approval.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">04</div>
                <div class="step-icon"><i class="fas fa-hammer"></i></div>
                <h3>Implementation</h3>
                <p>Skilled craftsmen and project oversight bring the design to life.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">05</div>
                <div class="step-icon"><i class="fas fa-check-circle"></i></div>
                <h3>Quality Check</h3>
                <p>Rigorous checks ensure workmanship and finish meet our high standards.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">06</div>
                <div class="step-icon"><i class="fas fa-door-open"></i></div>
                <h3>Final Handover</h3>
                <p>We unveil your transformed space, ready for you to enjoy.</p>
            </div>
        </div>
    </div>
</div>

<div class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Start Your Project?</h2>
            <p>Contact us today to schedule a free consultation and take the first step toward your dream space.</p>
            <a href="index.php?page=contact" class="btn btn-white btn-lg"><i class="fas fa-calendar-check"></i> Book
                Free Consultation</a>
        </div>
    </div>
</div>