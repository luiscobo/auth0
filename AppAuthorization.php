<?php

declare(strict_types=1);

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\NetworkException;
use Auth0\SDK\Exception\StateException;

final class ObapremiosAuthorization
{
    private string $domain = "centauroplay.us.auth0.com";
    /**
     * Client ID for the obapremios.com application
     *
     * @var string the client ID
     */
    private string $client_id = "";

    private string $client_secret = "";

    private string $cookie_secret = "";

    private string $route_url_callback = "https://obapremios.com/callback";

    private string $route_url_index = "https://obapremios.com/";

    private string $route_url_login;

    private string $route_url_logout;

    /**
     * An instance of our SDK's Auth0 configuration, so we could potentially make changes later.
     */
    private SdkConfiguration $configuration;

    /**
     * An instance of the Auth0 SDK.
     */
    private Auth0 $sdk;

    /**
     * Configure the application
     *
     * @param array|null $env
     */
    public function __construct(array $env = null)
    {
        $config = [];

        // Get the configuration
        if ($env != null) {
            $config = $env;
        }
        else {
            $config = [
                'domain' => $this->domain,
                'clientId' => $this->client_id,
                'clientSecret' => $this->client_secret,
                'cookieSecret' => $this->cookie_secret
            ];
        }

        // Setup the Auth0 SDK.
        $this->sdk = new Auth0($config);
    }

    /**
     * @param string $newClientId
     * @return void
     */
    public function setClientId(string $newClientId): void
    {
        $this->client_id = $newClientId;
    }

    /**
     * @param string $newClientSecret
     * @return void
     */
    public function setClientSecret(string $newClientSecret): void
    {
        $this->client_secret = $newClientSecret;
    }

    /**
     * @param string $route_url_callback
     */
    public function setRouteUrlCallback(string $route_url_callback): void
    {
        $this->route_url_callback = $route_url_callback;
    }

    /**
     * @param string $route_url_index
     */
    public function setRouteUrlIndex(string $route_url_index): void
    {
        $this->route_url_index = $route_url_index;
    }

    public function setRouteUrlLogin(string $uri): void
    {
        $this->route_url_login = $uri;
    }

    public function setRouteUrlLogout(string $uri): void
    {
        $this->route_url_logout = $uri;
    }

    /**
     * Process the current request and route it to the class handler.
     *
     * @param string $uri The new uri to redirect the end user to.
     */
    private function redirect(string $uri): void
    {
        header('Location: ' . $uri);
        exit;
    }

    /**
     * Allows to know if there is a user logged
     * @return bool
     */
    public function isUserLogged(): bool
    {
        // Retrieve current session credentials, if end user is signed in.
        $session = $this->sdk->getCredentials();

        if ($session === null) {
            // The user isn't logged in.
            return false;
        }

        // The user is logged in.
        return true;
    }

    /**
     * Renews the access token and ID token using an existing refresh token.
     *
     * @return void
     * @throws ConfigurationException
     * @throws NetworkException
     */
    public function renewToken(): void
    {
        // Retrieve current session credentials, if end user is signed in.
        $session = $this->sdk->getCredentials();

        // If a session is available, check if the token is expired.
        // @phpstan-ignore-next-line
        if ($session !== null && $session->accessTokenExpired) {
            try {
                // Token has expired, attempt to renew it.
                $this->sdk->renew();
            } catch (StateException $exception) {
                // There was an error during access token renewal. Clear the session.
                $this->sdk->clear();
                $session = null;
            }

        }
    }

    /**
     * Redirect to the Login Page
     *
     * @return void
     * @throws ConfigurationException
     */
    public function login(): void
    {
        // Clear the local session.
        $this->sdk->clear();

        // Redirect to Auth0's Universal Login page.
        $this->redirect($this->sdk->login($this->route_url_callback));
        exit;
    }

    /**
     * Delete any persistent data and clear out all stored
     * properties, and return the URI to Auth0 /logout endpoint for redirection.
     *
     * @throws ConfigurationException
     */
    public function logout(): void
    {
        // Clear the user's local session with our app,
        // then redirect them to the Auth0 logout endpoint to clear their Auth0 session.
        $this->redirect($this->sdk->logout($this->route_url_index));
        exit;
    }

    /**
     * Exchange authorization code for access, ID, and refresh tokens.
     *
     * @throws NetworkException
     * @throws StateException
     */
    public function completeAuthenticationFlow(): void
    {
        // Inform Auth0 we want to redirect to our /callback route, so we can perform the code exchange and setup the user session there.
        $this->sdk->exchange($this->route_url_callback);

        // Redirect to your application's index route.
        $this->redirect($this->route_url_index);
        exit;
    }

    /**
     * Get the value of a user profile attribute
     * @param string $field
     * @return string|null
     */
    public function getUserInfo(string $field): string|null
    {
        $user = $this->getUser();

        return $user[$field];
    }

    public function getUser(): ?array
    {
        $session = $this->sdk->getCredentials();
        if ($session === null) {
            return null;
        }

        return array(
            "user_id" => $session->user["sub"],
            "username" => $session->user["nickname"]
        );
    }

    public function getUserName(): string|null
    {
        return $this->getUserInfo("nickname");
    }

    public function getUserId(): string|null
    {
        return $this->getUserInfo("sub");
    }

    /**
     * Get ID token from an active session.
     *
     * @return string|null
     */
    public function getToken(): string|null
    {
        return $this->sdk->getIdToken();
    }


}