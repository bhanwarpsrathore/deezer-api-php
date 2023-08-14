<?php

declare(strict_types=1);

namespace DeezerAPI;

use DeezerAPI\Request;

class Session {

    protected $appId = '';
    protected $appSecret = '';
    protected $accessToken = '';
    protected $expirationTime = 0;
    protected $redirectUri = '';
    protected $scope = '';
    protected $request = null;

    /**
     * Constructor
     * Set up app credentials.
     *
     * @param string $appId The app ID.
     * @param string $appSecret Optional. The app secret.
     * @param string $redirectUri Optional. The redirect URI.
     * @param Request $request Optional. The Request object to use.
     */
    public function __construct($appId, $appSecret = '', $redirectUri = '', $request = null) {
        $this->setAppId($appId);
        $this->setAppSecret($appSecret);
        $this->setRedirectUri($redirectUri);

        $this->request = $request ?? new Request();
    }

    /**
     * Get the authorization URL.
     *
     * @param array|object $options Optional. Options for the authorization URL.
     * - string code_challenge Optional. A PKCE code challenge.
     * - array scope Optional. Scope(s) to request from the user.
     * - boolean show_dialog Optional. Whether or not to force the user to always approve the app. Default is false.
     * - string state Optional. A CSRF token.
     *
     * @return string The authorization URL.
     */
    public function getAuthorizeUrl($options = []) {
        $options = (array) $options;

        $parameters = [
            'app_id' => $this->getAppId(),
            'redirect_uri' => $this->getRedirectUri(),
            'perms' => isset($options['perms']) ? implode(',', $options['perms']) : 'basic_access,email',
        ];

        return Request::CONNECT_URL . '/oauth/auth.php?' . http_build_query($parameters, '', '&');
    }

    /**
     * Get the access token.
     *
     * @return string The access token.
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Get the app ID.
     *
     * @return string The app ID.
     */
    public function getAppId() {
        return $this->appId;
    }

    /**
     * Get the app secret.
     *
     * @return string The app secret.
     */
    public function getAppSecret() {
        return $this->appSecret;
    }

    /**
     * Get the access token expiration time.
     *
     * @return int A Unix timestamp indicating the token expiration time.
     */
    public function getTokenExpiration() {
        return $this->expirationTime;
    }

    /**
     * Get the client's redirect URI.
     *
     * @return string The redirect URI.
     */
    public function getRedirectUri() {
        return $this->redirectUri;
    }

    /**
     * Request an access token given an authorization code.
     *
     * @param string $authorizationCode The authorization code from Deezer.
     *
     * @return bool True when the access token was successfully granted, false otherwise.
     */
    public function requestAccessToken($authorizationCode) {
        $parameters = [
            'app_id' => $this->getAppId(),
            'secret' => $this->getAppSecret(),
            'code' => $authorizationCode,
            'output' => 'json'
        ];

        $response = $this->request->connect('GET', '/oauth/access_token.php', $parameters, []);
        $response = $response['body'];

        if (isset($response->access_token)) {
            $this->accessToken = $response->access_token;
            $this->expirationTime = time() + $response->expires;

            return true;
        }

        return false;
    }

    /**
     * Set the access token.
     *
     * @param string $accessToken The access token
     *
     * @return self
     */
    public function setAccessToken($accessToken) {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the app ID.
     *
     * @param string $appId The app ID.
     *
     * @return self
     */
    public function setAppId($appId) {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set the app secret.
     *
     * @param string $appSecret The app secret.
     *
     * @return self
     */
    public function setAppSecret($appSecret) {
        $this->appSecret = $appSecret;

        return $this;
    }

    /**
     * Set the client's redirect URI.
     *
     * @param string $redirectUri The redirect URI.
     *
     * @return self
     */
    public function setRedirectUri($redirectUri) {
        $this->redirectUri = $redirectUri;

        return $this;
    }
}
