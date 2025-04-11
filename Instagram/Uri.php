<?php

namespace InstagramPHP;

class Uri
{
    const BASE_URL = 'https://www.instagram.com/';
    const AUTH_URL = 'https://www.instagram.com/api/v1/web/accounts/login/ajax/';
    const REELS_URL = 'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids=';
    const USER_PROFILE_URL = 'https://i.instagram.com/api/v1/users/web_profile_info/?username=';
    const UA_IG_ANDROID = 'Mozilla/5.0 (Linux; Android 15; SH-M26 Build/SB653; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/134.0.6998.135 Mobile Safari/537.36 Instagram 373.0.0.46.67 Android (35/15; 490dpi; 1080x2432; SHARP; SH-M26; Quess; qcom; id_ID; 714264327)';
    const UA_OSX = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6333.211 Safari/537.36';
    const UA_LINUX = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36';
}
