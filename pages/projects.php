<div class="page-hero">
    <div class="container">
        <span class="section-tag">Our Portfolio</span>
        <h1>Our <span class="text-gradient">Projects</span></h1>
        <p>Explore our portfolio of beautifully designed interior spaces across Bangalore.</p>
    </div>
</div>

<div class="section projects-filter-section">
    <div class="container">
        <div class="projects-filter">
            <button class="filter-btn active" data-filter="all">All Projects</button>
            <?php $services = getActiveServices();
            foreach ($services as $service) { ?>
                <button class="filter-btn"
                    data-filter="<?php echo $service['id']; ?>"><?php echo htmlspecialchars($service['title']); ?></button>
            <?php } ?>
        </div>
    </div>
</div>

<div class="section projects-grid-section">
    <div class="container">
        <div class="projects-grid">
            <?php $projects = getActiveProjects();
            foreach ($projects as $project) {
                $images = json_decode($project['images'], true);
                $mainImage = isset($images[0]) ? $images[0] : 'default-project.jpg'; ?>
                <div class="project-card animate-on-scroll"
                    data-category="<?php echo htmlspecialchars($project['service_id']); ?>">
                    <div class="project-image">
                        <img src="assets/images/uploads/<?php echo htmlspecialchars($mainImage); ?>"
                            alt="<?php echo htmlspecialchars($project['title']); ?>">
                        <div class="project-overlay">
                            <div class="project-overlay-content">
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p><i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($project['location']); ?></p>
                                <button type="button" class="btn btn-white btn-sm view-details"
                                    data-project-id="<?php echo $project['id']; ?>">View Details</button>
                            </div>
                        </div>
                    </div>
                    <div class="project-info">
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($project['location']); ?></p>
                        <p><i class="fas fa-tag"></i> <?php echo htmlspecialchars($project['service_name']); ?></p>
                    </div>
                    <div class="project-detail-template" style="display:none;">
                        <div class="project-detail">
                            <div class="project-gallery">
                                <div class="main-image">
                                    <img src="assets/images/uploads/<?php echo htmlspecialchars($mainImage); ?>"
                                        alt="<?php echo htmlspecialchars($project['title']); ?>">
                                </div>
                                <div class="thumbnail-grid">
                                    <?php if (!empty($images)) {
                                        foreach ($images as $img) { ?>
                                            <img src="assets/images/uploads/<?php echo htmlspecialchars($img); ?>"
                                                alt="<?php echo htmlspecialchars($project['title']); ?>">
                                        <?php }
                                    } ?>
                                </div>
                            </div>
                            <div class="project-description">
                                <h2><?php echo htmlspecialchars($project['title']); ?></h2>
                                <div class="project-meta">
                                    <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong>
                                        <?php echo htmlspecialchars($project['location']); ?></p>
                                    <p><i class="fas fa-tag"></i> <strong>Service:</strong>
                                        <?php echo htmlspecialchars($project['service_name']); ?></p>
                                </div>
                                <p><?php echo $project['description']; ?></p>
                                <div class="project-cta">
                                    <a href="javascript:void(0)" class="btn btn-cta open-popup-enquiry">Start a Similar Project <i
                                            class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<!-- Project Modal -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeProjectModal">&times;</span>
        <div id="projectModalBody"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.project-card');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const filter = btn.getAttribute('data-filter');
                cards.forEach(card => {
                    const cat = card.getAttribute('data-category');
                    const show = (filter === 'all') || (filter === cat);
                    card.style.display = show ? '' : 'none';
                });
            });
        });

        const modal = document.getElementById('projectModal');
        const body = document.getElementById('projectModalBody');
        const closeBtn = document.getElementById('closeProjectModal');
        document.querySelectorAll('.view-details').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const card = e.target.closest('.project-card');
                if (!card) return;
                const template = card.querySelector('.project-detail-template');
                if (!template) return;
                body.innerHTML = template.innerHTML;
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                const mainImg = modal.querySelector('.main-image img');
                modal.querySelectorAll('.thumbnail-grid img').forEach(img => {
                    img.addEventListener('click', () => { if (mainImg) mainImg.src = img.src; });
                });
            });
        });
        function closeModal() { modal.style.display = 'none'; body.innerHTML = ''; document.body.style.overflow = ''; }
        closeBtn.addEventListener('click', closeModal);
        window.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
    });
</script>

<!-- CTA -->
<div class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Love What You See?</h2>
            <p>Let's create something amazing for your space. Start with a free consultation.</p>
            <a href="javascript:void(0)" class="btn btn-white btn-lg open-popup-enquiry"><i class="fas fa-calendar-check"></i> Book
                Free Consultation</a>
        </div>
    </div>
</div>