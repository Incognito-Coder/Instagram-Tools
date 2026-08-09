document.addEventListener("DOMContentLoaded", () => {
    // UI Element References
    const urlInput = document.getElementById("url-field");
    const userInput = document.getElementById("user-field");
    const btnSearchMedia = document.getElementById("button-search-media");
    const btnSearchProfile = document.getElementById("button-search-profile");
    const btnPaste = document.getElementById("btn-paste");
    const btnClear = document.getElementById("btn-clear");
    const loadingSpinner = document.getElementById("loading-spinner");

    const mediaCard = document.getElementById("media-card");
    const profileCard = document.getElementById("profile-card");
    const singleMediaContainer = document.getElementById("single-media-container");
    const thumbImg = document.getElementById("thumb-img");
    const thumbVideo = document.getElementById("thumb-video");
    const carouselContainer = document.getElementById("carousel-container");
    const carouselChild = document.getElementById("carousel-child");
    const captionContainer = document.getElementById("caption-container");
    const captionText = document.getElementById("caption-text");
    const downloadActions = document.getElementById("download-actions");

    // Session Modal elements
    const btnSaveSession = document.getElementById("btn-save-session");
    const inputSessionId = document.getElementById("input-sessionid");
    const inputCookieString = document.getElementById("input-cookiestring");
    const sessionDot = document.getElementById("session-dot");
    const sessionStatusText = document.getElementById("session-status-text");

    // Stats
    const likesCount = document.getElementById("likes-count");
    const viewsCount = document.getElementById("views-count");
    const commentsCount = document.getElementById("comments-count");
    const statLikes = document.getElementById("stat-likes");
    const statViews = document.getElementById("stat-views");
    const statComments = document.getElementById("stat-comments");

    // Check Session Status on page load
    checkSessionStatus();

    async function checkSessionStatus() {
        try {
            const res = await fetch("session.php?action=status");
            const data = await res.json();
            if (data.has_session) {
                if (sessionDot) sessionDot.className = "spinner-grow spinner-grow-sm text-success";
                if (sessionStatusText) sessionStatusText.textContent = "Session Active";
            } else {
                if (sessionDot) sessionDot.className = "spinner-grow spinner-grow-sm text-warning";
                if (sessionStatusText) sessionStatusText.textContent = "Session Setup";
            }
        } catch (e) {
            console.warn("Could not fetch session status:", e);
        }
    }

    if (btnSaveSession) {
        btnSaveSession.addEventListener("click", async () => {
            const sessionid = inputSessionId.value.trim();
            const cookieString = inputCookieString.value.trim();

            if (!sessionid && !cookieString) {
                showToast("Please enter a valid sessionid or cookie string.", "warning");
                return;
            }

            try {
                btnSaveSession.disabled = true;
                btnSaveSession.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i> Verifying...`;

                const response = await fetch("session.php?action=save", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ sessionid, cookie_string: cookieString }),
                });

                const data = await response.json();
                btnSaveSession.disabled = false;
                btnSaveSession.innerHTML = `<i class="fa-solid fa-floppy-disk me-1"></i> Save & Verify Session`;

                if (data.status) {
                    showToast(data.message || "Session saved!", "success");
                    checkSessionStatus();

                    // Close modal
                    const modalElem = document.getElementById("sessionModal");
                    if (modalElem) {
                        const modalInstance = bootstrap.Modal.getInstance(modalElem);
                        if (modalInstance) modalInstance.hide();
                    }
                } else {
                    showToast(data.message || "Failed to save session.", "error");
                }
            } catch (err) {
                btnSaveSession.disabled = false;
                btnSaveSession.innerHTML = `<i class="fa-solid fa-floppy-disk me-1"></i> Save & Verify Session`;
                showToast("Server communication error.", "error");
            }
        });
    }

    // Clipboard Paste Handler
    if (btnPaste) {
        btnPaste.addEventListener("click", async () => {
            try {
                const text = await navigator.clipboard.readText();
                if (text) {
                    urlInput.value = text.trim();
                    showToast("Pasted from clipboard!");
                }
            } catch (err) {
                showToast("Clipboard access denied or unsupported.", "warning");
            }
        });
    }

    if (btnClear) {
        btnClear.addEventListener("click", () => {
            urlInput.value = "";
            urlInput.focus();
        });
    }

    if (btnSearchMedia) {
        btnSearchMedia.addEventListener("click", () => fetchMediaData());
    }

    if (urlInput) {
        urlInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                fetchMediaData();
            }
        });
    }

    if (btnSearchProfile) {
        btnSearchProfile.addEventListener("click", () => fetchProfileData());
    }

    if (userInput) {
        userInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                fetchProfileData();
            }
        });
    }

    // Main Fetch Media Function
    async function fetchMediaData() {
        const link = urlInput.value.trim();
        if (!link) {
            showToast("Please enter a valid Instagram URL.", "warning");
            return;
        }

        resetUI();
        showLoading(true);

        try {
            const response = await fetch(`instagram.php?link=${encodeURIComponent(link)}`);
            const data = await response.json();

            showLoading(false);

            if (!data.status) {
                showToast(data.message || "Failed to fetch Instagram media.", "error");
                return;
            }

            renderMediaResult(data);
        } catch (error) {
            showLoading(false);
            showToast("Network or server error occurred.", "error");
            console.error("Fetch media error:", error);
        }
    }

    // Main Fetch Profile Function
    async function fetchProfileData() {
        const username = userInput.value.trim().replace("@", "");
        if (!username) {
            showToast("Please enter an Instagram username.", "warning");
            return;
        }

        resetUI();
        showLoading(true);

        try {
            const response = await fetch(`instagram.php?user=${encodeURIComponent(username)}`);
            const data = await response.json();

            showLoading(false);

            if (!data.status) {
                showToast(data.message || "Profile not found.", "error");
                return;
            }

            renderProfileResult(data);
        } catch (error) {
            showLoading(false);
            showToast("Failed to fetch profile information.", "error");
            console.error("Fetch profile error:", error);
        }
    }

    // Render Media Results
    async function renderMediaResult(data) {
        mediaCard.hidden = false;

        // Render Stats
        if (data.like !== null && data.like !== undefined) {
            likesCount.textContent = Number(data.like).toLocaleString();
            statLikes.hidden = false;
        }
        if (data.view !== null && data.view !== undefined) {
            viewsCount.textContent = Number(data.view).toLocaleString();
            statViews.hidden = false;
        }
        if (data.comment !== null && data.comment !== undefined) {
            commentsCount.textContent = Number(data.comment).toLocaleString();
            statComments.hidden = false;
        }

        // Render Caption
        if (data.caption) {
            captionText.innerHTML = data.caption.replace(/\n/g, "<br>");
            captionContainer.hidden = false;
            captionText.style.direction = isRTL(data.caption) ? "rtl" : "ltr";
        }

        // Single Image/Video
        if (data.type === "image" || data.type === "video") {
            singleMediaContainer.hidden = false;
            const proxiedPath = await getProxiedMediaUrl(data.file);

            if (data.type === "image") {
                thumbImg.src = proxiedPath || data.file;
                thumbImg.hidden = false;
            } else {
                thumbVideo.src = proxiedPath || data.file;
                thumbVideo.hidden = false;
            }

            createDownloadButton("Download " + (data.type === "video" ? "Video" : "Photo"), proxiedPath || data.file);
        }
        // Carousel / Sidecar
        else if (data.type === "side" && Array.isArray(data.data)) {
            carouselContainer.hidden = false;
            carouselChild.innerHTML = "";

            for (let i = 0; i < data.data.length; i++) {
                const item = data.data[i];
                const itemProxied = await getProxiedMediaUrl(item.file);

                const slide = document.createElement("div");
                slide.className = `carousel-item ${i === 0 ? "active" : ""}`;

                if (item.type === "video") {
                    slide.innerHTML = `<video class="d-block w-100 rounded" controls><source src="${itemProxied || item.file}" type="video/mp4"/></video>`;
                } else {
                    slide.innerHTML = `<img class="d-block w-100 rounded" src="${itemProxied || item.file}" alt="Slide ${i + 1}">`;
                }

                carouselChild.appendChild(slide);

                createDownloadButton(`Item ${i + 1} (${item.type})`, itemProxied || item.file);
            }

            // Initialize Bootstrap Carousel
            new bootstrap.Carousel(carouselContainer);
        }
    }

    // Render Profile Results
    async function renderProfileResult(data) {
        profileCard.hidden = false;

        const proxiedAvatar = await getProxiedMediaUrl(data.profile_hd);
        document.getElementById("profile-pic").src = proxiedAvatar || data.profile_hd;
        document.getElementById("profile-fullname").textContent = data.name || data.username;
        document.getElementById("profile-username").textContent = `@${data.username}${data.id ? ` • ID: ${data.id}` : ""}`;

        const formatStat = (val) => {
            if (val === null || val === undefined || val === '') return '0';
            if (typeof val === 'number') return val.toLocaleString();
            if (!isNaN(val)) return Number(val).toLocaleString();
            return String(val);
        };

        document.getElementById("profile-followers").textContent = formatStat(data.followers);
        document.getElementById("profile-following").textContent = formatStat(data.following);
        document.getElementById("profile-posts").textContent = formatStat(data.media_count);

        const bioElem = document.getElementById("profile-bio");
        if (data.bio) {
            bioElem.textContent = data.bio;
            bioElem.hidden = false;
        }

        const downloadBtn = document.getElementById("profile-download-btn");
        downloadBtn.href = proxiedAvatar || data.profile_hd;
    }

    // Helper: Proxy Media Download URL
    async function getProxiedMediaUrl(rawUrl) {
        if (!rawUrl) return null;
        try {
            const formData = new FormData();
            formData.append("file", rawUrl);

            const res = await fetch("proxy.php", {
                method: "POST",
                body: formData,
            });

            const json = await res.json();
            if (json.status && json.path) {
                return json.path;
            }
        } catch (e) {
            console.warn("Proxy request failed, falling back to raw url:", e);
        }
        return rawUrl;
    }

    // Helper: Create Download Button
    function createDownloadButton(label, fileUrl) {
        const btn = document.createElement("a");
        btn.className = "btn btn-action btn-sm me-2 mb-2";
        btn.href = fileUrl;
        btn.target = "_blank";
        btn.download = "";
        btn.innerHTML = `<i class="fa-solid fa-download me-1"></i> ${label}`;

        downloadActions.appendChild(btn);
    }

    // Reset UI State
    function resetUI() {
        mediaCard.hidden = true;
        profileCard.hidden = true;
        singleMediaContainer.hidden = true;
        thumbImg.hidden = true;
        thumbVideo.hidden = true;
        carouselContainer.hidden = true;
        captionContainer.hidden = true;
        downloadActions.innerHTML = "";

        statLikes.hidden = true;
        statViews.hidden = true;
        statComments.hidden = true;
    }

    function showLoading(visible) {
        loadingSpinner.hidden = !visible;
    }

    // RTL Text Detector
    function isRTL(str) {
        if (!str) return false;
        const persianArabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/;
        return persianArabicPattern.test(str);
    }

    // Toast Notifications
    function showToast(message, type = "info") {
        const toast = document.getElementById("toast-message");
        const toastText = document.getElementById("toast-text");
        toastText.textContent = message;

        toast.className = `toast-custom show ${type === "error" ? "border-danger" : type === "warning" ? "border-warning" : type === "success" ? "border-success" : "border-info"}`;

        setTimeout(() => {
            toast.classList.remove("show");
        }, 3500);
    }
});