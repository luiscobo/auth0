<?php

// Import the Composer Autoloader to make the SDK classes accessible:
require 'vendor/autoload.php';

// Import class App
require 'AppAuthorization.php';

// Load our environment variables from the .env file:
(Dotenv\Dotenv::createImmutable(__DIR__))->load();

// This is the configuration for our demo
$config = [
    'domain' => $_ENV['AUTH0_DOMAIN'],
    'clientId' => $_ENV['AUTH0_CLIENT_ID'],
    'clientSecret' => $_ENV['AUTH0_CLIENT_SECRET'],
    'cookieSecret' => $_ENV['COOKIE_SECRET']
];

// Now, we create the object to get the authenticator
$auth = new ObapremiosAuthorization($config);

// Import our router library:
use Steampixel\Route;

// Define route constants:
define("BASE_URL", rtrim($_ENV['BASE_URL'], '/'));

// We configure the routes for the system
$auth->setRouteUrlIndex(BASE_URL);
$auth->setRouteUrlCallback(BASE_URL . '/callback');
$auth->setRouteUrlLogin(BASE_URL . '/login');
$auth->setRouteUrlLogout(BASE_URL . '/logout');

// ------------------------------------------------------------------

// Route: root

Route::add('/', function() use ($auth) {
    if (!$auth->isUserLogged()) {
        // The user isn't logged in.
        echo '<p>Please <a href="/login">log in</a>.</p>';
        return;
    }

    // The user is logged in.
    echo '<pre>';
    print_r($auth->getUser());
    echo "User Name: " . $auth->getUserName();
    echo "\nUser ID: " . $auth->getUserId();
    echo '</pre>';

    echo '<p>You can now <a href="/logout">log out</a>.</p>';
});

// Route: login
Route::add('/login', function() use ($auth) {
    $auth->login();
    exit;
});

// Route: handling authentication callback
Route::add('/callback', function() use ($auth) {
    // Have the SDK complete the authentication flow
    // and redirect to the index
    $auth->completeAuthenticationFlow();
    exit;
});

// Route: Logging out
Route::add('/logout', function() use ($auth) {
    // Clear the user's local session with our app, then redirect them to the Auth0 logout endpoint to clear their Auth0 session.
    $auth->logout();
    exit;
});

Route::add("/hello", function () {
    echo "Hello world";
    exit;
});

Route::add('/phpinfo', function () {
    phpinfo();
    exit;
});

// This tells our router that we've finished configuring our routes, and we're ready to begin routing incoming HTTP requests:
Route::run('/');