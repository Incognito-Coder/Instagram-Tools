<?php

namespace InstagramPHP;

class Uri
{
    const BASE_URL = 'https://www.instagram.com/';
    const AUTH_URL = 'https://www.instagram.com/api/v1/web/accounts/login/ajax/';
    const REELS_URL = 'https://i.instagram.com/api/v1/feed/reels_media/?reel_ids=';
    const USER_PROFILE_URL = 'https://i.instagram.com/api/v1/users/web_profile_info/?username=';
    const UA_IG_ANDROID = 'Mozilla/5.0 (Linux; Android 14; SM-G998U1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Mobile Safari/537.36';
    const UA_OSX = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.6333.211 Safari/537.36';
    const UA_LINUX = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36';
}
