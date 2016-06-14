<form action="/login" method="post">
    <h2>Login</h2>
    <label><span>Username:</span><input name="username" type="text"></label><br>
    <label><span>Password:</span><input name="password" type="password"></label><br>
    <input type="submit"/>
</form>
<?php
    $oauth2 = ComponentsManager::Instance()->GetComponent('IOAuth2');
    $googleOAuthProvider = $oauth2->GetProvider('google');
    $loginWithGoogleUrl = $googleOAuthProvider->GetLoginUrl(null);
?>
<p><a href='<?=$loginWithGoogleUrl?>'>Login with Google</a></p>
<form action="/register" method="post">
    <h2>Register</h2>
    <label><span>Username:</span><input name="username" type="text"></label><br>
    <label><span>Password:</span><input name="password" type="password"></label><br>
    <label><span>Email:</span><input name="email" type="email"></label><br>
    <input type="submit"/>
</form>
