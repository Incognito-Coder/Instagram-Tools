# Instagram Basic Tools
Little php class for Instagram Scrapping.\
Telegram Bot Demo [@InstaTakerBot](https://t.me/InstaTakerBot) \
**Note** : \
_Don't forgot to set your own Coockie in Script._ [ **Deprecated** ]

```php
$_COOKIE = '';
```
### Great News
Since version 3.0 release you don't need to manually set cookies data, just login or run test.php file
```php
<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use InstagramPHP\Login as InstagramPHPLogin;

$session = new InstagramPHPLogin(new Client(), 'user', 'pass');
$session->SessionLogin();
```
## Local Deploy
First install [composer](https://getcomposer.org/) on your system,then run:
```bash
composer install
```
```bash
cd Instagram-Tools && php -S localhost:8000
```
then open http://localhost:8000/ in browser,now you can use PWA in your local webserver.
## Main Features :
* Return Account Info (Profile)
* Download Media
* Download Highlights
* Download Reels
* Download Story
# Changes

<details>
<summary>Click to see changelogs.</summary>

### v2.2
* Now is be PWA
### v2.5
* Sadly instagram fully closed previous ( **__a=1** ) method, so i used a newer one.
### v3.0
* Completely object oriented
* Codes rewrited from scratch
* All requests are now handled over Guzzle
* Fixed Highlight detection
### v3.2
* Now PWA can play carousel photos and videos
* some javascript bugs fixed
### v3.5
Note: *this is final build for 2023*
* Changed UserAgent somewhere
* Fixed login CSRF data
* Updated login ajax url
* New regex pattern for getting **media id**
* Bumped guzzle version to 7.8.1
### v3.6
* Fixed story scapping
* Some changes in Highlights regex pattern
### v3.7
* Returning post play count
* Bumped guzzle version to 7.9.2
### v3.8
* Fix of regex in getReelsID func
* Updated UserAgents
</details>

## Credits
Developer : Incognito Coder & Arash Ariaye \
if you enjoy my content, consider to buy me a coffee here:
1. Donate [ZarinPal](https://zarinp.al/@incognito)
2. USDT(**TRC20**) : `TD5XNhZPuVoc6ZnadbrQenuur3WWKwkFqV`
3. TRON(**TRX**) : `TD5XNhZPuVoc6ZnadbrQenuur3WWKwkFqV`
4. TON(**TON**) : `UQBAL2lkifBy7H8-3M7khJXu8w2TqjvJ8tSbhFRkNAR_7mQJ`
