<!-- Hero Slider -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

<div class="hero-section">
    <?php $slides = getActiveSliders(); ?>
    <div class="hero-slider swiper">
        <div class="swiper-wrapper">
            <?php foreach ($slides as $slide) {
                $img = isset($slide['image']) && $slide['image'] ? $slide['image'] : 'assets/images/about-image.jpg';
                if (strpos($img, 'http') !== 0 && strpos($img, 'assets/') !== 0) {
                    $img = 'assets/images/uploads/' . $img;
                }
                $badge = isset($slide['badge']) ? $slide['badge'] : '';
                $title = isset($slide['title']) ? $slide['title'] : '';
                $subtitle = isset($slide['subtitle']) ? $slide['subtitle'] : '';
                ?>
                <div class="swiper-slide">
                    <div class="hero-slide-image">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="Hero slide">
                        <div class="hero-overlay">
                            <?php if ($badge) { ?><span class="hero-badge"><i class="fas fa-star"></i>
                                    <?php echo $badge; ?></span><?php } ?>
                            <?php if ($title) { ?>
                                <h1><?php echo $title; ?></h1><?php } ?>
                            <?php if ($subtitle) { ?>
                                <p><?php echo $subtitle; ?></p><?php } ?>
                            <div class="hero-buttons">
                                <a href="index.php?page=contact" class="btn btn-cta btn-lg"><i
                                        class="fas fa-calendar-check"></i> Book Free Consultation</a>
                                <a href="index.php?page=projects" class="btn btn-white btn-lg"><i class="fas fa-images"></i>
                                    View Our Work</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Swiper('.hero-slider', {
            loop: true,
            speed: 900,
            autoplay: { delay: 5000, disableOnInteraction: false },
            pagination: { el: '.hero-slider .swiper-pagination', clickable: true },
            navigation: { nextEl: '.hero-slider .swiper-button-next', prevEl: '.hero-slider .swiper-button-prev' },
            slidesPerView: 1,
            effect: 'fade',
            fadeEffect: { crossFade: true },
        });
    });
</script>

<!-- Trust Bar -->
<div class="trust-bar">
    <div class="container">
        <div class="trust-bar-grid">
            <div class="trust-stat animate-on-scroll">
                <div class="stat-number" data-count="500">0</div>
                <div class="stat-label">Projects Delivered</div>
            </div>
            <div class="trust-stat animate-on-scroll">
                <div class="stat-number" data-count="1000">0</div>
                <div class="stat-label">Happy Clients</div>
            </div>
            <div class="trust-stat animate-on-scroll">
                <div class="stat-number" data-count="10">0</div>
                <div class="stat-label">Years Experience</div>
            </div>
            <div class="trust-stat animate-on-scroll">
                <div class="stat-number" data-count="5">0</div>
                <div class="stat-label">Cities Served</div>
            </div>
        </div>
    </div>
</div>

<!-- About Preview -->
<div class="section about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-image animate-on-scroll">
                <img src="assets/images/about-image.jpg" alt="About Living 360">
                <div class="about-image-badge">
                    <span class="badge-number">10+</span>
                    <span class="badge-text">Years of Excellence</span>
                </div>
            </div>
            <div class="about-text animate-on-scroll">
                <span class="section-tag">Who We Are</span>
                <h2>Crafting Spaces That <span class="text-gradient">Inspire Living</span></h2>
                <p>At Living 360 Interiors, we believe that your space should be a reflection of who you are. With over
                    a decade of experience, our team of talented designers creates spaces that are not only beautiful
                    but also functional and tailored to your lifestyle.</p>
                <p>We take a holistic approach to interior design, considering every aspect — from layout and lighting
                    to furniture and accessories — to create harmonious environments that enhance your quality of life.
                </p>
                <div class="about-features">
                    <div class="about-feature"><i class="fas fa-check-circle"></i> Personalized Design Solutions</div>
                    <div class="about-feature"><i class="fas fa-check-circle"></i> End-to-End Project Management</div>
                    <div class="about-feature"><i class="fas fa-check-circle"></i> Premium Quality Materials</div>
                    <div class="about-feature"><i class="fas fa-check-circle"></i> On-Time Project Delivery</div>
                </div>
                <a href="index.php?page=about" class="btn btn-primary">Learn More About Us <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Services Section -->
<div class="section services-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">What We Offer</span>
            <h2>Our <span class="text-gradient">Design Services</span></h2>
            <p>Comprehensive interior design services tailored to transform your space into something extraordinary.</p>
        </div>

        <div class="services-grid-home">
            <?php
            $services = getActiveServices();
            foreach ($services as $service) {
                $title = trim($service['title']);
                $excerpt = strip_tags($service['description']);
                $excerpt = strlen($excerpt) > 120 ? substr($excerpt, 0, 120) . '...' : $excerpt;
                $img = isset($service['image']) && $service['image'] ? 'assets/images/uploads/' . $service['image'] : 'assets/images/about-image.jpg';
                echo '<div class="service-card-home animate-on-scroll" data-service-id="' . (int) $service['id'] . '">
                        <div class="service-card-image">
                            <img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($title) . '">
                            <div class="service-card-overlay">
                                <a href="index.php?page=contact" class="btn btn-cta btn-sm">Get Quote</a>
                            </div>
                        </div>
                        <div class="service-card-body">
                            <h3>' . htmlspecialchars($title) . '</h3>
                            <p>' . htmlspecialchars($excerpt) . '</p>
                            <a href="index.php?page=services" class="service-link">Learn More <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>';
            }
            ?>
        </div>

        <div class="text-center" style="margin-top:32px;">
            <a href="index.php?page=services" class="btn btn-primary btn-lg">Explore All Services <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="section process-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Our Process</span>
            <h2>How We <span class="text-gradient">Bring Your Vision to Life</span></h2>
            <p>A simple, transparent process from the first call to your dream space handover.</p>
        </div>
        <div class="process-timeline">
            <div class="process-step animate-on-scroll">
                <div class="step-number">01</div>
                <div class="step-icon"><i class="fas fa-comments"></i></div>
                <h3>Free Consultation</h3>
                <p>Share your ideas, budget & timeline. We listen, understand, and advise — completely free.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">02</div>
                <div class="step-icon"><i class="fas fa-pencil-ruler"></i></div>
                <h3>Design & Planning</h3>
                <p>Our team creates detailed 3D designs, material selections, and a full project plan.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">03</div>
                <div class="step-icon"><i class="fas fa-hammer"></i></div>
                <h3>Expert Execution</h3>
                <p>Skilled craftsmen bring the design to life with premium materials and quality workmanship.</p>
            </div>
            <div class="process-connector"></div>
            <div class="process-step animate-on-scroll">
                <div class="step-number">04</div>
                <div class="step-icon"><i class="fas fa-key"></i></div>
                <h3>Handover & Enjoy</h3>
                <p>We hand over your transformed space on time, ready for you to enjoy every moment.</p>
            </div>
        </div>
        <div class="text-center" style="margin-top:32px;">
            <a href="index.php?page=contact" class="btn btn-cta btn-lg">Start Your Journey Today <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Projects -->
<div class="section projects-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Our Portfolio</span>
            <h2>Recent <span class="text-gradient">Projects</span></h2>
            <p>Explore some of our finest interior design transformations.</p>
        </div>

        <div class="projects-grid">
            <?php
            $projects = getActiveProjects(6);
            foreach ($projects as $project) {
                $images = json_decode($project['images'], true);
                $mainImage = isset($images[0]) ? $images[0] : 'default-project.jpg';

                echo '
                <div class="project-card animate-on-scroll">
                    <div class="project-image">
                        <img src="assets/images/uploads/' . $mainImage . '" alt="' . $project['title'] . '">
                        <div class="project-overlay">
                            <div class="project-overlay-content">
                                <h3>' . $project['title'] . '</h3>
                                <p><i class="fas fa-map-marker-alt"></i> ' . $project['location'] . '</p>
                                <a href="index.php?page=projects#' . $project['id'] . '" class="btn btn-white btn-sm">View Project</a>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            }
            ?>
        </div>

        <div class="text-center" style="margin-top:32px;">
            <a href="index.php?page=projects" class="btn btn-primary btn-lg">View All Projects <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Testimonials -->
<div class="section testimonials-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Client Love</span>
            <h2>What Our <span class="text-gradient">Clients Say</span></h2>
            <p>Real stories from homeowners and businesses who trusted us with their spaces.</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p><?php echo isset($testimonial1) ? $testimonial1 : 'Living 360 transformed our home beyond our imagination. The attention to detail and quality of work was exceptional.'; ?>
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar"><i class="fas fa-user-circle"></i></div>
                    <div>
                        <strong><?php echo isset($author1) ? $author1 : 'Happy Client'; ?></strong>
                        <span><?php echo isset($role1) ? $role1 : 'Homeowner'; ?></span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p><?php echo isset($testimonial2) ? $testimonial2 : 'Professional team, on-time delivery, and stunning results. Highly recommend Living 360 for any interior project.'; ?>
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar"><i class="fas fa-user-circle"></i></div>
                    <div>
                        <strong><?php echo isset($author2) ? $author2 : 'Satisfied Client'; ?></strong>
                        <span><?php echo isset($role2) ? $role2 : 'Business Owner'; ?></span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card animate-on-scroll">
                <div class="testimonial-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                        class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
                <p><?php echo isset($testimonial3) ? $testimonial3 : 'From concept to execution, Living 360 made the entire process seamless. Our office space looks world-class now.'; ?>
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar"><i class="fas fa-user-circle"></i></div>
                    <div>
                        <strong><?php echo isset($author3) ? $author3 : 'Delighted Client'; ?></strong>
                        <span><?php echo isset($role3) ? $role3 : 'CEO'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Why Choose Us -->
<div class="section why-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Why Us</span>
            <h2>Why Choose <span class="text-gradient">Living 360?</span></h2>
            <p>We combine creativity, expertise, and a client-centered approach to deliver exceptional results.</p>
        </div>
        <div class="why-grid">
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-medal"></i></span>
                <div>
                    <h4>Award-Winning Design</h4>
                    <p>Recognized with multiple industry awards for design excellence and innovation.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-layer-group"></i></span>
                <div>
                    <h4>End-to-End Solutions</h4>
                    <p>From concept to completion — one team handles everything for you.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-users"></i></span>
                <div>
                    <h4>Experienced Team</h4>
                    <p>Decades of combined experience in residential and commercial design.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-rupee-sign"></i></span>
                <div>
                    <h4>Transparent Pricing</h4>
                    <p>No hidden costs. Detailed quotations with clear breakdowns upfront.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-clock"></i></span>
                <div>
                    <h4>On-Time Delivery</h4>
                    <p>We respect your time and deliver every project within the agreed timeline.</p>
                </div>
            </div>
            <div class="why-item animate-on-scroll">
                <span class="icon-badge round"><i class="fas fa-shield-alt"></i></span>
                <div>
                    <h4>10-Year Warranty</h4>
                    <p>Quality backed by warranty. We stand behind every project we deliver.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Banner -->
<div class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <span class="cta-badge"><i class="fas fa-fire"></i> Limited Slots This Month</span>
            <h2>Ready to Transform Your Space?</h2>
            <p>Get a free consultation and personalized design proposal. No obligation, no hidden fees.</p>
            <div class="cta-buttons">
                <a href="index.php?page=contact" class="btn btn-white btn-lg"><i class="fas fa-calendar-check"></i> Book
                    Free Consultation</a>
                <a href="tel:+919845061004" class="btn btn-outline-white btn-lg"><i class="fas fa-phone"></i> Call
                    Now</a>
            </div>
        </div>
    </div>
</div>

<!-- Blog Preview -->
<div class="section blogs-section">
    <div class="container">
        <div class="section-title">
            <span class="section-tag">Our Blog</span>
            <h2>Design <span class="text-gradient">Inspiration & Tips</span></h2>
            <p>Stay up to date with the latest trends and expert advice in interior design.</p>
        </div>

        <div class="blogs-grid">
            <?php
            $blogs = getActiveBlogs(3);
            foreach ($blogs as $blog) {
                echo '
                <div class="blog-card animate-on-scroll">
                    <div class="blog-image">
                        <img src="assets/images/uploads/' . $blog['featured_image'] . '" alt="' . $blog['title'] . '">
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span><i class="fas fa-calendar"></i> ' . date('M d, Y', strtotime($blog['created_at'])) . '</span>
                            <span><i class="fas fa-user"></i> ' . $blog['author'] . '</span>
                        </div>
                        <h3>' . $blog['title'] . '</h3>
                        <p>' . $blog['excerpt'] . '</p>
                        <a href="index.php?page=blogs&slug=' . $blog['slug'] . '" class="read-btn">Read Article &rarr;</a>
                    </div>
                </div>
                ';
            }
            ?>
        </div>

        <div class="text-center" style="margin-top:24px;">
            <a href="index.php?page=blogs" class="btn btn-primary">View All Articles <i
                    class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>