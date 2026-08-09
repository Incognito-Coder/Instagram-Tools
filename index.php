<?php
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>InstaTaker - Premium Instagram Downloader & Profile Finder</title>

    <!-- Meta Tags for PWA & SEO -->
    <meta name="description" content="Download Instagram Posts, Reels, Stories, Highlights, and Profile HD Photos effortlessly. Free, fast, and secure.">
    <meta name="theme-color" content="#090d16">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="InstaTaker">

    <!-- Icons & Manifest -->
    <link rel="apple-touch-icon" href="icon/instagram_128.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/instagram_32.png">
    <link rel="manifest" href="manifest.json">

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Mobile Responsive Styles -->
    <link rel="stylesheet" href="css/styles.css?v=4.5">
</head>

<body>
    <div class="container py-3 py-sm-4 px-3 flex-grow-1" style="max-width: 880px;">
        <!-- Header & Top Bar -->
        <header class="mb-4 text-center">
            <div class="header-top-bar">
                <button type="button" class="btn btn-sm btn-secondary-custom d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#sessionModal" id="btn-session-status">
                    <span class="spinner-grow spinner-grow-sm text-warning" role="status" id="session-dot"></span>
                    <span id="session-status-text">Check Session</span>
                    <i class="fa-solid fa-gear ms-1"></i>
                </button>
            </div>

            <div class="d-inline-flex align-items-center justify-content-center gap-2 mb-1">
                <i class="fa-brands fa-instagram fs-1 text-danger" style="background: linear-gradient(135deg, #f09433, #dc2743, #bc1888); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h1 class="brand-header m-0">InstaTaker</h1>
                <span class="brand-badge ms-1">v4.5</span>
            </div>
            <p class="text-muted m-0 fs-6 fw-medium">Download Posts, Reels, Stories, Highlights & HD Profiles</p>
        </header>

        <!-- Mode Switcher Tabs -->
        <div class="text-center mb-3 mb-sm-4">
            <ul class="nav nav-pills-custom" id="searchTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-panel" type="button" role="tab">
                        <i class="fa-solid fa-cloud-arrow-down me-2"></i>Media Downloader
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-panel" type="button" role="tab">
                        <i class="fa-solid fa-id-card me-2"></i>Profile Finder
                    </button>
                </li>
            </ul>
        </div>

        <!-- Search Control Box -->
        <div class="glass-card p-3 p-sm-4 mb-4">
            <div class="tab-content" id="searchTabContent">
                <!-- Media Search Panel -->
                <div class="tab-pane fade show active" id="media-panel" role="tabpanel">
                    <label for="url-field" class="form-label mb-2">
                        Enter Instagram Post, Reel, Story or Highlight URL
                    </label>
                    <div class="search-box-wrapper mb-2">
                        <div class="search-input-row">
                            <i class="fa-solid fa-link text-info ms-1"></i>
                            <input type="text" class="search-input" id="url-field" placeholder="https://www.instagram.com/p/..." autocomplete="off">
                            <button type="button" class="btn btn-sm btn-secondary-custom me-1" id="btn-paste" title="Paste from clipboard">
                                <i class="fa-solid fa-paste"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary-custom me-1" id="btn-clear" title="Clear input">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-action" id="button-search-media">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>Fetch Media
                        </button>
                    </div>
                </div>

                <!-- Profile Search Panel -->
                <div class="tab-pane fade" id="profile-panel" role="tabpanel">
                    <label for="user-field" class="form-label mb-2">
                        Enter Instagram Username (e.g. instagram or @cristiano)
                    </label>
                    <div class="search-box-wrapper mb-2">
                        <div class="search-input-row">
                            <i class="fa-solid fa-at text-info ms-1"></i>
                            <input type="text" class="search-input" id="user-field" placeholder="e.g. instagram" autocomplete="off">
                        </div>
                        <button type="button" class="btn-action" id="button-search-profile">
                            <i class="fa-solid fa-user-check me-1"></i>Fetch Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-spinner" class="text-center py-5" hidden>
            <div class="spinner-pulse mx-auto mb-3"></div>
            <p class="text-white fw-bold animate-pulse">Fetching Instagram data, please wait...</p>
        </div>

        <!-- Profile Result Display Card -->
        <div id="profile-card" class="glass-card p-3 p-sm-4 mb-4" hidden>
            <div class="text-center mb-4">
                <div class="profile-avatar-container mb-3">
                    <div class="profile-avatar-wrapper">
                        <img id="profile-pic" class="profile-avatar" src="" alt="Profile Picture">
                    </div>
                </div>
                <h3 id="profile-fullname" class="fw-extrabold text-white mb-1 fs-4"></h3>
                <p id="profile-username" class="text-info fs-6 fw-semibold mb-3"></p>

                <!-- Stats Grid -->
                <div class="d-flex justify-content-center gap-2 gap-sm-3 mb-4 flex-wrap">
                    <div class="stat-card">
                        <div class="stat-value" id="profile-followers">0</div>
                        <div class="stat-label">Followers</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="profile-following">0</div>
                        <div class="stat-label">Following</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="profile-posts">0</div>
                        <div class="stat-label">Posts</div>
                    </div>
                </div>

                <p id="profile-bio" class="caption-box mb-4 text-start" hidden></p>
                
                <a id="profile-download-btn" href="#" target="_blank" download class="btn-action">
                    <i class="fa-solid fa-download me-2"></i>Download HD Profile Photo
                </a>
            </div>
        </div>

        <!-- Media Result Display Card -->
        <div id="media-card" class="glass-card p-3 p-sm-4 mb-4" hidden>
            <!-- Stats & Type Bar -->
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                <span id="media-type-badge" class="stat-badge badge-primary-custom">
                    <i class="fa-solid fa-photo-film me-1"></i>Media Preview
                </span>
                <div class="d-flex gap-2" id="media-stats-group">
                    <span id="stat-likes" class="stat-badge" hidden>
                        <i class="fa-solid fa-heart text-danger me-1"></i><span id="likes-count">0</span>
                    </span>
                    <span id="stat-views" class="stat-badge" hidden>
                        <i class="fa-solid fa-eye text-info me-1"></i><span id="views-count">0</span>
                    </span>
                    <span id="stat-comments" class="stat-badge" hidden>
                        <i class="fa-solid fa-comment text-warning me-1"></i><span id="comments-count">0</span>
                    </span>
                </div>
            </div>

            <!-- Single Media Preview (Image / Video) -->
            <div id="single-media-container" class="media-preview-box mb-3" hidden>
                <img id="thumb-img" class="img-fluid rounded" hidden />
                <video id="thumb-video" class="w-100 rounded" controls hidden></video>
            </div>

            <!-- Multi-item Carousel Container -->
            <div id="carousel-container" class="carousel slide media-preview-box mb-3" data-bs-ride="false" hidden>
                <div class="carousel-inner" id="carousel-child"></div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-container" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-container" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>

            <!-- Caption -->
            <div id="caption-container" class="caption-box mb-3" hidden>
                <p id="caption-text" class="m-0"></p>
            </div>

            <!-- Download Action Buttons -->
            <div class="d-flex flex-wrap gap-2 pt-2 border-top border-secondary-subtle" id="download-actions">
            </div>
        </div>
    </div>

    <!-- Instagram Session Settings Modal -->
    <div class="modal fade" id="sessionModal" tabindex="-1" aria-labelledby="sessionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered px-2">
            <div class="modal-content glass-card border-0 text-white" style="background: rgba(15, 23, 42, 0.98); backdrop-filter: blur(24px);">
                <div class="modal-header border-bottom border-secondary-subtle">
                    <h5 class="modal-title font-semibold d-flex align-items-center gap-2 text-white" id="sessionModalLabel">
                        <i class="fa-brands fa-instagram text-danger fs-4"></i> Instagram Session Setup
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-white">
                    <!-- Quick Guide -->
                    <div class="p-3 mb-3 rounded" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255,255,255,0.15);">
                        <h6 class="fw-bold mb-2 text-warning"><i class="fa-solid fa-key me-1"></i> Quick 3-Step Setup Guide:</h6>
                        <ol class="small mb-0 ps-3" style="color: #cbd5e1;">
                            <li class="mb-1">Log into <a href="https://www.instagram.com" target="_blank" class="text-info text-decoration-none fw-bold">instagram.com</a> in your browser.</li>
                            <li class="mb-1">Press <kbd>F12</kbd> &rarr; Go to <strong>Application</strong> &rarr; <strong>Cookies</strong> &rarr; <code>instagram.com</code>.</li>
                            <li>Copy the value of <strong><code class="text-info">sessionid</code></strong> and paste it below.</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <label for="input-sessionid" class="form-label text-white fw-bold">Instagram Session ID (sessionid)</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary fw-medium" id="input-sessionid" placeholder="e.g. 5421098%3AFa9x..." autocomplete="off">
                    </div>

                    <div class="accordion mb-3" id="advancedCookieAccordion">
                        <div class="accordion-item bg-transparent border-0">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent text-white small p-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced">
                                    <i class="fa-solid fa-code me-1"></i> Advanced: Or paste full Cookie String
                                </button>
                            </h2>
                            <div id="collapseAdvanced" class="accordion-collapse collapse" data-bs-parent="#advancedCookieAccordion">
                                <div class="accordion-body px-0 pt-2">
                                    <textarea class="form-control bg-dark text-white border-secondary small fw-medium" id="input-cookiestring" rows="3" placeholder="sessionid=...; csrftoken=...; mid=..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary-subtle">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn-action" id="btn-save-session">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save & Verify Session
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="toast-message" class="toast-custom">
        <i class="fa-solid fa-circle-info text-info fs-5"></i>
        <span id="toast-text">Notification message</span>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container px-3">
            <p class="m-0 mb-1 fw-medium" style="color: #cbd5e1;">
                © 2026 InstaTaker | Designed with <i class="fas fa-heart text-danger"></i> for high performance scraper tools
            </p>
            <small style="color: #94a3b8;">Fast & Secure Instagram Downloader & PWA</small>
        </div>
    </footer>

    <!-- Bootstrap 5 JS & Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/script.js?v=4.5"></script>
</body>

</html>