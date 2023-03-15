<?php

declare(strict_types=1);

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\NetworkException;
use Auth0\SDK\Exception\StateException;
use JetBrains\PhpStorm\NoReturn;

final class ObapremiosAuthorization
{
    private string $domain = "centauroplay.us.auth0.com";
    /**
     * Client ID for the obapremios.com application
     *
     * @var string the client ID
     */
    private string $client_id = "8dDhsS9O4LXsMRQ1LvRRYF9HUVutTEFG";

    private string $client_secret = "MRP0-ZPju4p0P6WjJA0gsoEG1OMGla8BMGCCYrgiPFdQAnlGMY74dsic7Ka0J0IW";

    private string $cookie_secret = "1fb6e74453c8af30e8e5b77c5913bc886ee35ab55fc98044137134471547f90c";

    private string $route_url_callback = "https://obapremios.com";

    private string $route_url_index = "https://obapremios.com";

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
     * @throws ConfigurationException
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

        // Build the SdkConfiguration
        $this->configuration = new SdkConfiguration($config);

        // Add 'offline_access' to scopes to ensure we get a renew token.
        $this->configuration->pushScope('offline_access');

        // Setup the Auth0 SDK.
        $this->sdk = new Auth0($this->configuration);
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

    /**
     * Process the current request and route it to the class handler.
     *
     * @param string $uri The new uri to redirect the end user to.
     */
    #[NoReturn] private function redirect(string $uri): void
    {
        header('Location: ' . $uri, true, 303);
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
     */
    #[NoReturn] public function login(): void
    {
        // Clear the local session.
        $this->sdk->clear();

        // Redirect to Auth0's Universal Login page.
        $this->redirect($this->route_url_callback);
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
        $this->sdk->logout($this->route_url_index);
    }

    /**
     * Exchange authorization code for access, ID, and refresh tokens.
     *
     * @throws NetworkException
     * @throws StateException
     */
    #[NoReturn] public function completeAuthenticationFlow(): void
    {
        // Inform Auth0 we want to redirect to our /callback route, so we can perform the code exchange and setup the user session there.
        $this->sdk->exchange($this->route_url_callback);

        // Redirect to your application's index route.
        $this->redirect($this->route_url_index);
    }

    /**
     * Get the value of a user profile attribute
     * @param string $field
     * @return string|null
     */
    public function getUserInfo(string $field): string|null
    {
        $session = $this->sdk->getCredentials();
        if ($session === null) {
            return null;
        }

        return $session->user[$field];
    }

    /**
     * @return string|null
     */
    public function getUserFamilyName(): string|null
    {
        return $this->getUserInfo("family_name");
    }

    /**
     * @return string|null
     */
    public function getUserFirstName(): string | null
    {
        return $this->getUserInfo("given_name");
    }

    public function getUserEmail(): string|null
    {
        return $this->getUserInfo("email");
    }

    public function getUserName(): string|null
    {
        return $this->getUserInfo("username");
    }

    public function getUserId(): string|null
    {
        return $this->getUserInfo("user_id");
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