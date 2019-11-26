# Základní příklad užití

```php

<?php

require_once 'vendor/autoload.php';

// Zalozime si autentizacni process
$authorizationProcess = new HomeCredit\OneClickApi\RestClient\AuthorizationProcess\AuthTokenAuthorizationProcess(
	'024242tech',
	'024242tech'
);

// Pripravime si tovarnu na HTTP clienta
$httpClientFactory = new \HomeCredit\OneClickApi\HttpClientFactory([
	'baseUrl' => 'https://apicz-test.homecredit.net/verdun-train/'
]);

// Vytvorime si REST clienta
$client = new \HomeCredit\OneClickApi\RestClient\Application(
	$httpClientFactory, 
	$authorizationProcess
);

// Vytvorime si objekt requestu pro zalozeni application. V tomto pripade z JSONu, ulozenem na filesystemu. Pro dekodovani json je pouzita knihovna z Nette
$json = Nette\Utils\Json::decode(
	file_get_contents(__DIR__ . '/tests/fixtures/CreateApplicationRequest.json'),
	\Nette\Utils\Json::FORCE_ARRAY
);
$request = \HomeCredit\OneClickApi\Entity\CreateApplicationRequest::fromArray($json);

// Zalozime application pres API. V response budeme mi objekt HomeCredit\OneClickApi\Entity\CreateApplicationResponse
$response = $client->create($request);
var_dump($response->getId());
```

# Instalace knihovny a spuštění testů

Tento dokument předpokládá prostředí MS Windows a instalaci PHP do adresáře c:\php5.6.38. Dále předpokládáme funkční GIT v příkazové řádce.

## Instalace PHP5.6

1. Stáhnout binárku pro windows z https://windows.php.net/download#php-5.6. Například: https://windows.php.net/downloads/releases/php-5.6.38-nts-Win32-VC11-x86.zip
2. Rozbalit do adresáře `c:\php5.6.38`
3. Zkopírovat `php.ini-development` do `php.ini`
4. Stáhnout poslední verzi cacert.pem pro cUrl z https://curl.haxx.se/ca/cacert.pem a zkopírovat do `c:\php5.6.38`
5. V `php.ini` povolit extenze: `php_curl.dll`, `php_mbstring.dll`, `php_openssl.dll` (nejjednodušší cesta je odkomentovat příslušný řádek v php.ini a cestu k extenzi nastavit jako plnou. Například `extension=c:\php5.6.38\ext\php_openssl.dll`
6. V `php.ini` nastavit cestu k `cacert.pem` - `curl.cainfo = c:\php5.6.38\cacert.pem`
7. Nastavit cestu k php binárce do proměnné prostředí PATH. To provedeme tak, že v konzoli pustíme: `SET PATH=c:\php5.6.38\;%PATH%`
8. Nyní by mělo v příkazové řádce fungovat: `php -v`

## Instalace OneClick knihovny
1. Rozbalíme projekt například do adresáře `c:\temp\oneclick-api-client`
2. Vstoupíme do adresáře: `cd oneclick-api-client` a následně do podaresáře `oneclick-api-client-master`, který je součástí staženého ZIP souboru
3. Stáhneme instalátor composeru `php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"`
4. `php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"`
5. Spustíme instalátor composeru `php composer-setup.php`
6. Smažeme již nepotřebný instalátor composeru `php -r "unlink('composer-setup.php');"`
7. Nainstalujeme závislosti pomocí `php composer.phar install`
8. Nyní můžeme spustit unit testy`php composer.phar test-unit`
9. A integrační testy `php composer.phar test-int`

