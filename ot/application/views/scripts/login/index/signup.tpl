{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="signup"}
You can create a new account here.  A password will be created for you and sent to 
the email address you provide.  If you already have an account for {$appTitle}, you can  
<a href="{$sitePrefix}/login/?realm={$realm}">click here</a> to log in.<br /><br />
Enter your requested user ID below, as well as your email address.
{/editable}
<br /><br />
<div id="signupForm">
    {$form}
</div>