{if count($messages) != 0}
<div class="message">
{foreach from=$messages item=m}
{$m}<br />
{/foreach}
</div>
{/if}

Please log in to access your account. <br /><br />
<form action="" method="post" id="login" class="checkRequiredFields">

<table class="form">
    <tbody>
    <tr>
        <td class="label"><label for="userId">User ID:</label></td>
        <td class="value"><input type="text" id="userId" name="userId" value="" class="required" /></td>
    </tr>
    <tr>
        <td class="label"><label for="password">Password:</label></td>
        <td class="value"><input type="password" id="password" name="password" class="required" /><br /><br />
        <a href="{$sitePrefix}/login/index/forgot/">Click here</a> if you have forgotten your password.</td>
    </tr>
    </tbody>
</table>
<input type="submit" value="Login" />
</form>
<br /><br />
<form method="POST" action="{$sitePrefix}/login/index/signup/" id="signup" class="checkRequiredFields">
<h3>Don't have an account?</h3>
If you want to submit a proposal, you can sign up for an account.  A confirmation
email will be sent to the email you provided that contains your password.<br /><br />
<table class="form">
    <tr>
        <td><label for="userId">User ID:</label></td>
        <td><input type="text" id="userId" name="userId" value="" class="required" />
        (Should only contain alphanumeric characters)</td>
    </tr>
    <tr>
        <td><label for="email">Email Address:</label></td>
        <td><input type="text" id="email" name="email" value="" class="required" /></td>
    </tr>
</table>
<input type="submit" value="Sign Up Now!" />
</form>        