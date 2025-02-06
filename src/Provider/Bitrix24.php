<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\Bitrix24IdentityProviderException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Bitrix24 extends AbstractProvider
{
    use BearerAuthorizationTrait;

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'user_id';

    /**
     * Domain
     *
     * @var string
     */
    public $domain;

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->domain . '/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://oauth.bitrix.info/oauth/token/';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->domain . '/rest/user.current';
    }

    /**
     * @param AccessToken $token
     * @return mixed|null
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $authUrl = $this->domain . '/oauth/token/';
        $authUrl .= '?grant_type=authorization_code';
        $authUrl .= '&client_id=' . $this->clientId;
        $authUrl .= '&client_secret=' . $this->clientSecret;
        $authUrl .= '&code=' . $token;

        $factory = $this->getRequestFactory();
        $request = $factory->getRequestWithOptions(self::METHOD_GET, $authUrl);
        $authResponse = $this->getParsedResponse($request);

        if (false === is_array($authResponse)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        if(empty($authResponse['access_token'])) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server.'
            );
        }

        $url = $this->getResourceOwnerDetailsUrl($token);
        $url .= '?auth=' . $authResponse['access_token'];

        $request = $factory->getRequestWithOptions(self::METHOD_GET, $url);
        $userResponse = $this->getParsedResponse($request);

        if (false === is_array($userResponse)) {
            throw new UnexpectedValueException(
                'Invalid response received from Authorization Server. Expected JSON.'
            );
        }

        return isset($userResponse['result']) ? $userResponse['result'] : null;
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [''];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array             $data     Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw Bitrix24IdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw Bitrix24IdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param  array       $response
     * @param  AccessToken $token
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new Bitrix24ResourceOwner($response);
    }
}
