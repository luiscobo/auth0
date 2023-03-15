#  Log-in in the Backend

This version of ```AppAuthorization.php``` allows to use Auth0 in PHP. 

1. Installing the Auth0 PHP SDK

```bash
composer require auth0/auth0-php
```

2. Create the AppAuthorization object: it is a good idea to store the configuration in the ```.env``` file of the project.

```php
$config = ['domain' => $_ENV['DOMAIN'],
    'clientId' => $_ENV['CLIENT_ID'],
    'clientSecret' => $_ENV['CLIENT_SECRET']);
$auth = new ObapremiosAuthorization(config);
```

3. Add Login to the application:

   In the login process, you have to do:
   
   ```php
   $auth->login();
   ```
   
   And after the login process is finished, it is necessary to do the following:
   
   ```php
   $auth->completeAuthenticationFlow();
   ```
   
4. Add Logout to your Application

   ```php
   $auth->logout();
   ```
   
5. Get user information: it is possible get additional information about the logged user with the methods
   * `getUserInfo($attribute)`
   * `getUserFamilyName()`
   * `getUserFirstName()`
   * `getUserName()`
   * `getUserId()`
