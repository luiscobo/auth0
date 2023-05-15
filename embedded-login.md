# Embedded Login Auth0 in the FrontEnd

In order to use Auth0 as login management from the front end, we need to follow the next steps:

1. Installation

```bash
npm install auth0-js
```

2. After installing, you should import the module in your javascript file:

```javascript
import auth0 from 'auth0-js';
```

3. Initialization: Initialize a new instance of the *Auth0* Embedded Login application as follows

```html
<script type="text/javascript">
  var auth = new auth0.WebAuth({
    domain:       '{yourDomain}',
    clientID:     '{yourClientId}'
  });
</script>
```

4. To sign up a user, use the `signup` method. 

```javascript
auth.signup({
    connection: 'obapremios',
    email: user.email,
    password: user.password,
    user_metadata: { full_name: 'xxxxxx', telephone: 'yyyyyy' }
}, function (err) {
    if (err) return alert('Something went wrong: ' + err.message);
    return alert('success signup without login!')
});
```

5. In order to login a user, and to get an access toke, we have the `login` method.

```javascript
auth.login({
  realm: 'obapremios',
  username: user.email,
  password: user.password,
});
```

6. To extract a token after a successful login

```javascript
auth.parseHash({ hash: window.location.hash }, function(err, authResult) {
  if (err) {
    return console.log(err);
  }

  auth.client.userInfo(authResult.accessToken, function(err, user) {
    // Now you have the user's information
  });
});
```

7. When a user logs in, Auth0 returns at most, the following two items are returned as token:

* `accessToken`: 	An Access Token for the API, specified by the audience
* `expiresIn`:	A string containing the expiration time (in seconds) of the accessToken
* `idToken`:	An ID Token JWT containing user profile information

8. To log out a user, use the `logout()` method. 

```javascript
auth.logout({
  returnTo: 'some url here',
  clientID: 'some client ID here'
});
```
