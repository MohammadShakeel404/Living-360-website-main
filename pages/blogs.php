<div class="page-hero">
    <div class="container">
        <span class="section-tag">Insights & Inspiration</span>
        <h1>Our <span class="text-gradient">Blog</span></h1>
        <p>Expert tips, trends, and inspiration to help you design your perfect space.</p>
    </div>
</div>

<div class="section blogs-list-section">
    <div class="container">
        <?php
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $blog = getBlogBySlug($slug);

            if ($blog) {
                echo '
                <div class="blog-detail">
                    <div class="blog-header">
                        <h1>' . $blog['title'] . '</h1>
                        <div class="blog-meta">
                            <p><i class="fas fa-user"></i> ' . $blog['author'] . '</p>
                            <p><i class="fas fa-calendar"></i> ' . date('F d, Y', strtotime($blog['created_at'])) . '</p>
                        </div>
                    </div>
                    <div class="blog-image">
                        <img src="assets/images/uploads/' . $blog['featured_image'] . '" alt="' . $blog['title'] . '">
                    </div>
                    <div class="blog-content">
                        ' . $blog['content'] . '
                    </div>
                    <div class="blog-cta-bar">
                        <p><strong>Inspired by this article?</strong> Let\'s bring these ideas to your space.</p>
                        <a href="index.php?page=contact" class="btn btn-cta">Get Free Consultation <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div style="margin-top:24px;">
                        <a href="index.php?page=blogs" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Blog</a>
                    </div>
                </div>
                ';
            }
        } else {
            echo '<div class="blogs-grid">';
            $blogs = getActiveBlogs();
            foreach ($blogs as $blog) {
                echo '
                <div class="blog-card animate-on-scroll">
                    <div class="blog-image">
                        <img src="assets/images/uploads/' . $blog['featured_image'] . '" alt="' . $blog['title'] . '">
                    </div>
                    <div class="blog-content">
                        <div class="blog-meta">
                            <span><i class="fas fa-user"></i> ' . $blog['author'] . '</span>
                            <span><i class="fas fa-calendar"></i> ' . date('M d, Y', strtotime($blog['created_at'])) . '</span>
                        </div>
                        <h3>' . $blog['title'] . '</h3>
                        <p>' . $blog['excerpt'] . '</p>
                        <a href="index.php?page=blogs&slug=' . $blog['slug'] . '" class="read-btn">Read Article &rarr;</a>
                    </div>
                </div>
                ';
            }
            echo '</div>';
        }
        ?>
    </div>
</div>

<div class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Transform Your Space?</h2>
            <p>Get expert design advice tailored to your home or office. Start with a free consultation.</p>
            <a href="index.php?page=contact" class="btn btn-white btn-lg"><i class="fas fa-calendar-check"></i> Book
                Free Consultation</a>
        </div>
    </div>
</div>