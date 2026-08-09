<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
use InstagramPHP\InstagramAPI;
use InstagramPHP\Login as InstagramPHPLogin;

if (file_exists(__DIR__ . '/.env')) {
    Dotenv::createImmutable(__DIR__)->safeLoad();
}

$isCli = (PHP_SAPI === 'cli');

// ============================================================================
// 1. WEB BROWSER AJAX POST HANDLER
// ============================================================================
if (!$isCli && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true) ?? $_POST;

    $username = trim($inputData['username'] ?? '');
    $password = trim($inputData['password'] ?? '');
    $sessionId = trim($inputData['sessionid'] ?? '');
    $cookieString = trim($inputData['cookie_string'] ?? '');

    $cookieFile = __DIR__ . '/cookies.txt';

    // Strategy A: Process Session ID or Cookie String
    if (!empty($sessionId) || !empty($cookieString)) {
        $jar = new FileCookieJar($cookieFile, true);
        if (!empty($sessionId)) {
            $sessionId = preg_replace('/^sessionid=/i', '', $sessionId);
            $cookie = new SetCookie([
                'Name' => 'sessionid',
                'Value' => $sessionId,
                'Domain' => '.instagram.com',
                'Path' => '/',
                'Secure' => true,
                'HttpOnly' => true,
            ]);
            $jar->setCookie($cookie);
        }

        if (!empty($cookieString)) {
            foreach (explode(';', $cookieString) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2) {
                    $jar->setCookie(new SetCookie([
                        'Name' => trim($parts[0]),
                        'Value' => trim($parts[1]),
                        'Domain' => '.instagram.com',
                        'Path' => '/',
                        'Secure' => true,
                    ]));
                }
            }
        }

        $jar->save($cookieFile);

        echo json_encode([
            'status' => true,
            'message' => 'Session ID saved successfully! Redirecting to main app...',
            'redirect' => 'index.php'
        ]);
        exit;
    }

    // Strategy B: Automated Login with Username & Password
    if (!empty($username) && !empty($password)) {
        if (file_exists($cookieFile)) {
            @unlink($cookieFile);
        }

        try {
            $session = new InstagramPHPLogin(new Client(['timeout' => 15]), $username, $password);
            $result = $session->SessionLogin();

            if ($result) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Login Successful! Authenticated and saved session cookies. Redirecting...',
                    'redirect' => 'index.php'
                ]);
                exit;
            }
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Please provide Username & Password OR a valid Session ID.']);
    exit;
}

// ============================================================================
// 2. COMMAND LINE (CLI) HANDLER
// ============================================================================
if ($isCli) {
    $username = $_ENV['username'] ?? getenv('username') ?? null;
    $password = $_ENV['password'] ?? getenv('password') ?? null;
    $sessionId = $_ENV['SESSION_ID'] ?? getenv('SESSION_ID') ?? null;
    $cookieString = $_ENV['COOKIE_STRING'] ?? getenv('COOKIE_STRING') ?? null;

    echo "=======================================================\n";
    echo "           InstaTaker Authentication Helper            \n";
    echo "=======================================================\n\n";

    if ($sessionId || $cookieString) {
        echo "[*] Found manual session cookies in .env. Creating cookies.txt...\n";
        $jar = new FileCookieJar(__DIR__ . '/cookies.txt', true);
        if ($sessionId) {
            $jar->setCookie(new SetCookie([
                'Name' => 'sessionid',
                'Value' => trim($sessionId),
                'Domain' => '.instagram.com',
                'Path' => '/',
                'Secure' => true,
                'HttpOnly' => true,
            ]));
        }
        if ($cookieString) {
            foreach (explode(';', $cookieString) as $pair) {
                $parts = explode('=', trim($pair), 2);
                if (count($parts) === 2) {
                    $jar->setCookie(new SetCookie([
                        'Name' => trim($parts[0]),
                        'Value' => trim($parts[1]),
                        'Domain' => '.instagram.com',
                        'Path' => '/',
                        'Secure' => true,
                    ]));
                }
            }
        }
        $jar->save(__DIR__ . '/cookies.txt');
        echo "[+] Session cookies saved successfully. You can now use InstaTaker scraper & API!\n";
        exit(0);
    }

    if (!$username || !$password) {
        echo "[!] Error: 'username' and 'password' (or 'SESSION_ID') must be set in .env file.\n";
        exit(1);
    }

    if (file_exists(__DIR__ . '/cookies.txt')) {
        unlink(__DIR__ . '/cookies.txt');
        echo "[*] Cleared previous cookies.txt file.\n";
    }

    try {
        echo "[*] Attempting automated Instagram login for user: {$username}...\n";
        $session = new InstagramPHPLogin(new Client(['timeout' => 15]), $username, $password);
        $result = $session->SessionLogin();

        if ($result) {
            echo "[+] Success! Authenticated and saved session cookies to cookies.txt.\n";
        }
    } catch (\Exception $e) {
        echo "\n[-] Login Notice / Checkpoint Triggered:\n";
        echo "    " . $e->getMessage() . "\n\n";
        echo "-------------------------------------------------------\n";
        echo " HOW TO RESOLVE THIS:\n";
        echo " Option A: Add SESSION_ID to .env file and re-run php auth.php.\n";
        echo " Option B: Open http://localhost:8000/auth.php in your web browser.\n";
        echo "-------------------------------------------------------\n";
        exit(1);
    }

    exit(0);
}

// ============================================================================
// 3. WEB BROWSER PAGE RENDER (GET Request)
// ============================================================================
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>InstaTaker - Account Login & Authentication</title>

    <!-- Bootstrap 5.3 & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Custom Glassmorphism Styles -->
    <link rel="stylesheet" href="css/styles.css?v=4.5">
</head>

<body>
    <div class="container py-4 px-3 flex-grow-1 d-flex flex-column justify-content-center" style="max-width: 520px;">
        <!-- Header -->
        <div class="text-center mb-4">
            <a href="index.php" class="text-decoration-none d-inline-flex align-items-center justify-content-center gap-2 mb-2">
                <i class="fa-brands fa-instagram fs-1 text-danger" style="background: linear-gradient(135deg, #f09433, #dc2743, #bc1888); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                <h1 class="brand-header m-0">InstaTaker</h1>
            </a>
            <p class="text-muted m-0 fs-6 fw-medium">Instagram Account Login & Session Authentication</p>
        </div>

        <!-- Glassmorphism Login Card -->
        <div class="glass-card p-4 shadow-lg mb-4">
            <!-- Mode Tabs -->
            <ul class="nav nav-pills-custom w-100 mb-4" id="loginTabs" role="tablist">
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link w-100 active" id="account-tab" data-bs-toggle="tab" data-bs-target="#account-panel" type="button" role="tab">
                        <i class="fa-solid fa-lock me-1"></i> Account Login
                    </button>
                </li>
                <li class="nav-item flex-fill text-center" role="presentation">
                    <button class="nav-link w-100" id="session-tab" data-bs-toggle="tab" data-bs-target="#session-panel" type="button" role="tab">
                        <i class="fa-solid fa-key me-1"></i> Session ID
                    </button>
                </li>
            </ul>

            <!-- Alert Message Box -->
            <div id="alert-box" class="alert d-none mb-3" role="alert"></div>

            <div class="tab-content" id="loginTabContent">
                <!-- Account Login Form -->
                <div class="tab-pane fade show active" id="account-panel" role="tabpanel">
                    <form id="form-account-login" autocomplete="off">
                        <div class="mb-3">
                            <label for="login-username" class="form-label text-white">Instagram Username</label>
                            <div class="search-box-wrapper py-2">
                                <i class="fa-solid fa-user text-info ms-1"></i>
                                <input type="text" class="search-input" id="login-username" placeholder="your_username" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="login-password" class="form-label text-white">Instagram Password</label>
                            <div class="search-box-wrapper py-2">
                                <i class="fa-solid fa-key text-info ms-1"></i>
                                <input type="password" class="search-input" id="login-password" placeholder="••••••••••••" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-action w-100" id="btn-submit-account">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Log In to Instagram
                        </button>
                    </form>
                </div>

                <!-- Session ID Token Setup Form -->
                <div class="tab-pane fade" id="session-panel" role="tabpanel">
                    <div class="p-3 mb-3 rounded" style="background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255,255,255,0.15);">
                        <h6 class="fw-bold mb-2 text-warning"><i class="fa-solid fa-lightbulb me-1"></i> Instant Session Bypass:</h6>
                        <p class="small text-muted mb-0" style="color: #cbd5e1 !important;">
                            Log into <strong>instagram.com</strong> in your browser &rarr; Press <kbd>F12</kbd> &rarr; Go to Application &rarr; Cookies &rarr; Copy <code>sessionid</code> and paste below.
                        </p>
                    </div>

                    <form id="form-session-login" autocomplete="off">
                        <div class="mb-4">
                            <label for="login-sessionid" class="form-label text-white">Instagram Session ID (sessionid)</label>
                            <div class="search-box-wrapper py-2">
                                <i class="fa-solid fa-shield-halved text-info ms-1"></i>
                                <input type="text" class="search-input" id="login-sessionid" placeholder="e.g. 5421098%3AFa9x..." required>
                            </div>
                        </div>

                        <button type="submit" class="btn-action w-100" id="btn-submit-session">
                            <i class="fa-solid fa-floppy-disk me-2"></i> Save Session & Continue
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Back to App Link -->
        <div class="text-center">
            <a href="index.php" class="text-info text-decoration-none fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to InstaTaker Main App
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS & Login Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const alertBox = document.getElementById("alert-box");

            function showAlert(msg, type = "danger") {
                alertBox.className = `alert alert-${type} d-block border-0 text-white shadow-sm small fw-semibold`;
                alertBox.textContent = msg;
            }

            function hideAlert() {
                alertBox.className = "alert d-none";
            }

            // Handle Account Login Submission
            const formAccount = document.getElementById("form-account-login");
            const btnAccount = document.getElementById("btn-submit-account");

            formAccount.addEventListener("submit", async (e) => {
                e.preventDefault();
                hideAlert();

                const username = document.getElementById("login-username").value.trim();
                const password = document.getElementById("login-password").value;

                btnAccount.disabled = true;
                btnAccount.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-2"></i> Authenticating...`;

                try {
                    const res = await fetch("auth.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ username, password }),
                    });

                    const data = await res.json();
                    btnAccount.disabled = false;
                    btnAccount.innerHTML = `<i class="fa-solid fa-right-to-bracket me-2"></i> Log In to Instagram`;

                    if (data.status) {
                        showAlert(data.message || "Logged in successfully!", "success");
                        setTimeout(() => {
                            window.location.href = data.redirect || "index.php";
                        }, 1200);
                    } else {
                        showAlert(data.message || "Login failed.", "danger");
                    }
                } catch (err) {
                    btnAccount.disabled = false;
                    btnAccount.innerHTML = `<i class="fa-solid fa-right-to-bracket me-2"></i> Log In to Instagram`;
                    showAlert("Network error or connection failed.", "danger");
                }
            });

            // Handle Session ID Form Submission
            const formSession = document.getElementById("form-session-login");
            const btnSession = document.getElementById("btn-submit-session");

            formSession.addEventListener("submit", async (e) => {
                e.preventDefault();
                hideAlert();

                const sessionid = document.getElementById("login-sessionid").value.trim();

                btnSession.disabled = true;
                btnSession.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-2"></i> Saving Session...`;

                try {
                    const res = await fetch("auth.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ sessionid }),
                    });

                    const data = await res.json();
                    btnSession.disabled = false;
                    btnSession.innerHTML = `<i class="fa-solid fa-floppy-disk me-2"></i> Save Session & Continue`;

                    if (data.status) {
                        showAlert(data.message || "Session saved!", "success");
                        setTimeout(() => {
                            window.location.href = data.redirect || "index.php";
                        }, 1200);
                    } else {
                        showAlert(data.message || "Failed to save session.", "danger");
                    }
                } catch (err) {
                    btnSession.disabled = false;
                    btnSession.innerHTML = `<i class="fa-solid fa-floppy-disk me-2"></i> Save Session & Continue`;
                    showAlert("Network error or connection failed.", "danger");
                }
            });
        });
    </script>
</body>

</html>
