{editable id='authSection'}
{if $loggedInUser == ''} 
    <b>You are not Logged in!</b> <a href="{$sitePrefix}/login/">Log in</a> here.
{else}
    Logged in as {$loggedInUser} via {$loggedInRealm}  &nbsp;|&nbsp;
    {if $myAccount}
    <a href="{$sitePrefix}/account/">My Account</a> &nbsp;|&nbsp;
    {/if}
    <a href="{$sitePrefix}/login/index/logout/">Sign Out</a>
{/if}
{/editable}