php-oauth
=========

This is a PHP Guzzle3 plugin for dealing with CB OAuth2.

## Example Usage

```php
use CareerBuilder\OAuth2\OAuth2Plugin;
use CareerBuilder\OAuth2\TokenFactory;
use CareerBuilder\OAuth2\NullTokenStorage;

// create Guzzle client as you normally do

$client = new Client('https://api.careerbuilder.com');

// register the OAuth2Plugin

$config = array(
    'base_url' => 'https://www.careerbuilder.com',
    'client_id' => '',
    'client_secret' => '',
    'shared_secret' => ''
)
$client->addSubscriber(new OAuth2Plugin(new TokenFactory($config), new NullTokenStorage()));

// do whatever you normally do with Guzzle

$request = $client->get('/corporate/geography/validate');
$request->getQuery()->set('query', 'Atlanta');
$response = $request->send();
```

See more in [usage.php](https://github.com/cbdr/php-oauth/blob/master/usage.php).
