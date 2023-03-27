# Bitrix24 Provider for OAuth 2.0 Client

This package provides Bitrix24 OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require batiscaff/oauth2-bitrix24
```

## Usage

Usage is similar to the basic OAuth client, using `\Batiscaff\OAuth2\Client\Provider\Bitrix24` as the provider.

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\Bitrix24([
    'domain'            => 'https://some-bitrix24-host.com',
    'clientId'          => '{bitrix24-client-id}',
    'clientSecret'      => '{bitrix24-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getName());
        printf('Your email: %s', $user->getEmail());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Bitrix24 authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['user','user.userfield','lists','im','iblock'] // array or string;
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the 
[following scopes are available](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=99&LESSON_ID=2280).

- bizproc
- calendar
- call
- catalog
- configuration.import
- contact_center
- crm
- department
- disk
- documentgenerator
- crm.documentgenerator
- entity
- telephony
- im
- imbot
- imopenlines
- intranet
- landing
- lists
- log
- mailservice
- messageservice
- mobile
- pay_system
- placement
- pull
- rpa
- sale
- sonet_group
- task
- timeman
- user
- user_brief
- user_basic
- user.userfield
- userfieldconfig
- userconsent
- landing_cloud
- delivery
- rating
- smile
- iblock
- configuration.import
- salescenter
- cashbox
- forum
- pull_channel

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## License

The MIT License (MIT). Please see [License File](https://github.com/Batiscaff/oauth2-bitrix24/blob/master/LICENSE.md) for more information.
