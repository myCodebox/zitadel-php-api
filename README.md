# Zitadel PHP API

With this library you can easily communicate between your PHP projects and Zitadel over a serviceUser. The wrapper is based on the [Zitadel API](https://zitadel.com/docs/apis/introduction/).

## Installation

```bash
git clone https://github.com/myCodebox/zitadel-php-api.git
cd zitadel-php-api
composer install
```

## Documentation

You can find a API documentation [here](https://mycodebox.github.io/zitadel-php-api/).

## Usage

**Create a new user**
```php
use ZitadelPhpApi\ZitadelPhpApi\Create;

$settings = [
    "domain" => "",
    "serviceUserToken" => "",
    "userToken" => ""
];

// Create a new user
$user = new Create($settings);
$user->setUserId('d654e6ba-70a3-48ef-a95d-37c8d8a7901a');
$user->setUserName('minnie-mouse');
$user->setOrganization('d654e6ba-70a3-48ef-a95d-37c8d8a7901a', 'zitadel.org');
$user->setName('Minnie', 'Mouse');
$user->setNickName('Mini');
$user->setDisplayName('Minnie Mouse');
$user->setLanguage('en');
$user->setGender('GENDER_FEMALE');
$user->setEmail('mini@mouse');
$user->setPhone('+41791234567');
$user->addMetaData('my-key1', 'This is my test value1');
$user->addMetaData('my-key2', 'This is my test value2');
$user->addMetaData('my-key3', 'This is my test value3');
$user->setPassword('Secr3tP4ssw0rd!', true);
$user->addIDPLink('1', '6516849804890468048461403518', 'user@external.com');
$user->addIDPLink('2', '6516849804890468048461403518', 'user@external.com');
$user->addIDPLink('3', '6516849804890468048461403518', 'user@external.com');

try {
    dump($user->create());
} catch (Exception $e) {
    echo $e->getMessage();
}
```

**Add user avatar**
```php
use ZitadelPhpApi\User\Avatar;

$settings = [
    "domain" => "",
    "serviceUserToken" => "",
    "userToken" => ""
];

// Add avatar
$avatar = new Avatar($settings);
$avatar->setUserId('313871513763708931');
$avatar->setImagePath('avatar_500x500.png');
try {
    $avatar->add();
} catch (Exception $e) {
    echo $e->getMessage();
}
```

**Remove user avatar**
```php
use ZitadelPhpApi\User\Avatar;

$settings = [
    "domain" => "",
    "serviceUserToken" => "",
    "userToken" => ""
];

// Remove avatar
$avatar = new Avatar($settings);    
$avatar->setUserId('313871513763708931');
try {
    $avatar->remove();
} catch (Exception $e) {
    echo $e->getMessage();
}
```

## Credits

- [Zitadel](https://github.com/zitadel/zitadel)
- [PHP-QRCode](https://github.com/chillerlan/php-qrcode)

## License

Zitadel PHP API is released under the Apache 2.0 License.