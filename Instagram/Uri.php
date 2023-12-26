<?php

namespace InstagramPHP;

class Uri
{
    const BASE_URL = 'https://www.instagram.com/';
    const AUTH_URL = 'https://www.instagram.com/api/v1/web/accounts/login/ajax/';
    const REELS_URL = 'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids=';
    const USER_PROFILE_URL = 'https://i.instagram.com/api/v1/users/web_profile_info/?username=';
    const UA_IG_ANDROID = 'Mozilla/5.0 (Linux; Android 11; SM-N985F Build/RP1A.200720.012; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/100.0.4896.58 Mobile Safari/537.36 Instagram 227.0.0.12.117 Android (30/11; 420dpi; 1180x2123; samsung; SM-N985F; c2s; exynos990;';
    const UA_OSX = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36';
    const UA_LINUX = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36';
}
