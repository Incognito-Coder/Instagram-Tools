# Instagram Basic Tools
Little php class for Instagram Scrapping.\
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
```bash
cd Instagram-Tools && php -S localhost:8000
```
then open http://localhost:8000/ in browser,now you can use PWA in your local webserver.
## Main Features :
* Return Account Info (Profile)
* Download Media
* Download Highlights
* Download Story
# Changes
### v2.2
* Now is be PWA
### v2.5
* Sadly instagram fully closed previous ( **__a=1** ) method, so i used a newer one.
### v3.0
* Completely object oriented
* Codes rewrited from scratch
* All requests are now handled over Guzzle
* Fixed Highlight detection
## Credits
Developer : Incognito Coder & Arash Ariaye \
if you enjoy my content, consider to buy me a coffee here:
1. Donate [ZarinPal](https://zarinp.al/@incognito)
2. USDT(**TRC20**) : `TWCgNwinrFXpvVr7iDAbx1qBCBitJ6ttB7`
3. BTC : `343x2iWnWBFjyLn6TUQTjdGW8ZExMD8mmp`
