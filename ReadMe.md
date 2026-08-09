# InstaTaker (Instagram Tools)

**InstaTaker** is a high-performance PHP API library & modern Progressive Web Application (PWA) for scraping and downloading Instagram content including Posts, Reels, Stories, Highlights, and Profile HD Photos.

---

## Key Features

- **Media Downloader**: Download single images, HD MP4 videos, and multi-item carousel posts.
- **Story & Highlight Saver**: Fetch active user stories and highlights.
- **Profile HD Viewer**: Retrieve account metadata, bio, followers count, and high-resolution profile pictures.
- **Secure Media Proxy**: Stream and download CDN media files directly without browser CORS restrictions or referrer leaks.
- **Proxy Server Support**: Environment-based proxy setup (`PROXY_HOST`, `PROXY_PORT`) for high-volume or bypass fetching.
- **Modern Glassmorphic Dark UI**: Mobile-friendly PWA interface with instant clipboard integration, loading states, and carousel previews.

---

## Environment Configuration (`.env`)

Create a `.env` file in the root directory (refer to `.env.example`):

```env
username=your_instagram_username
password=your_instagram_password
PROXY_HOST=127.0.0.1
PROXY_PORT=8080
```

*Note: Environment variables are automatically loaded by `Dotenv` across all entry points.*

---

## Installation & Setup

1. **Install Dependencies**:
   ```bash
   composer install
   ```

2. **Authenticate & Generate Session Cookies**:
   Run the authentication gateway via CLI or open `http://localhost:8000/auth.php` in your browser:
   ```bash
   php auth.php
   ```

3. **Start Local Development Server**:
   ```bash
   php -S localhost:8000
   ```
   Open `http://localhost:8000/` in your web browser.

---

## API Endpoints

### 1. Fetch Media / Post / Story / Highlight
- **Endpoint**: `GET /instagram.php?link={INSTAGRAM_URL}`
- **Example**: `GET /instagram.php?link=https://www.instagram.com/p/C_samplecode/`
- **Response**:
  ```json
  {
    "status": true,
    "type": "video",
    "caption": "Sample post caption",
    "like": 1250,
    "comment": 45,
    "view": 5400,
    "file": "https://..."
  }
  ```

### 2. Fetch User Profile
- **Endpoint**: `GET /instagram.php?user={USERNAME}`
- **Example**: `GET /instagram.php?user=instagram`
- **Response**:
  ```json
  {
    "status": true,
    "type": "profile",
    "name": "Instagram",
    "username": "instagram",
    "bio": "Discover what's next on Instagram.",
    "profile_hd": "https://...",
    "followers": 670000000,
    "following": 50
  }
  ```

### 3. Media Download Proxy
- **Endpoint**: `POST /proxy.php`
- **Body**: `file={MEDIA_URL}`
- **Response**: `{"status": true, "path": "temp/e7d8...jpg"}`

---

## Project Structure

```
Instagram-Tools/
├── css/
│   └── styles.css          # Modern Glassmorphic styling system
├── Instagram/
│   ├── InstagramAPI.php     # Main Guzzle HTTP API client
│   ├── Login.php            # Instagram auth & session handler
│   └── Uri.php              # API endpoints and User-Agent constants
├── js/
│   ├── main.js              # Service Worker registration script
│   └── script.js            # Frontend application logic & UI binding
├── index.php                # Main PWA web interface
├── instagram.php            # Primary JSON API route controller
├── proxy.php                # Secure media proxy & temp cache manager
├── auth.php                 # CLI & Web Browser authentication gateway
├── sw.js                    # Service Worker cache controller
├── manifest.json            # PWA manifest metadata
├── composer.json            # Composer package manifest
└── ReadMe.md                # Project documentation
```

---

## License & Credits

Developed by **Incognito Coder** & **Alireza Ahmand**.  
Licensed under the **MIT License**.
