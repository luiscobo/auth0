#  Log-in in the FrontEnd

In order to use Auth0 as login management from the front end, we need to follow 
the next steps:

1. Include a reference to **auth0** library in the HTML file where you are going
   to invoke the login process

```html
<script src="https://cdn.auth0.com/js/auth0-spa-js/2.0/auth0-spa-js.production.js"></script>
```

2. In order to initialize the login process, the following information is necessary to 
   leverage the API

```javascript
const domain = "centauroplay.us.auth0.com";
const clientId = "8dDhsS9O4LXsMRQ1LvRRYF9HUVutTEFG";
```

3. After that, we begin the login process in the main html file. It is a good idea use
   the ```window.onload``` event handler to configure the client. 

```javascript
let authClient = null;
const configureClient = async () => {
    authClient = await auth0.createAuth0Client({
        domain: domain,
        clientId: clientId
    });
};

window.onload = async () => {
    await configureClient();
}
```

4. To login in to the application, the main page must redirect to the **Auth0 Universal Login Page**.
   In this page, the user can register in the application or log in, and the information about the
   process is accessed through the ```authClient``` object in javascript.

   The method ```login``` starts the login process, and the parameter ```redirect_uri``` allows us
   to indicate the page that will be shown after the user logs in to the application. The following
   code show an example of the login process:

```javascript
const loginProcess = async () => {
  await authClient.loginWithRedirect({
     authorizationParams: {
        redirect_uri: "<ANY VALID URL>"
    }
  });
};
```

5. When a user logs in, Auth0 returns at most, the following two items are returned as token:

   * ```access_token```: used to call the API functions
   * ```id_token```: are used to cache user profile information. By default, an ID token is valid for 
   36000 seconds (10 hours).
   * ```expires_in```: the number of seconds before the Access Token expires.
   
   We can use the following methods to get the previous tokens and the logged-in user information:

   * ```authClient.isAuthenticated()```: if we want to know if the user is logged in or not.
   * ```authClient.getTokenSilently()```: Fetches a new access token
   * ```authClient.getUser()```: Returns the user information if available
   
   For example, if you want to get a new JWT Access Token, you can doit in the following way
   
   ```javascript
   const token = await authClient.getTokenSilently();
   ```
   
   In order to obtain additional information about the logged user, that information is stored in the 
   user object. For example, this code demonstrate the way to print the information:
   
   ```javascript
  //logged in. you can get the user profile like this:
  const user = await authClient.getUser();
  console.log("First Name: " + user.given_name);
  console.log("Family Name: " + user.family_name);
  console.log("Telephone: " + user.phone_number);
   ```

6. In order to start the log-out process, it is necessary to call the ```authClient.logout()``` method
   passing a valid ```return-to``` URL.

```javascript
const logoutProcess = () => {
   authClient.logout({
      logoutParams: {
         returnTo: "<ANY VALID URL>"
      }
   });
};
```
