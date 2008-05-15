{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="account"}This is the data which is associated with your user
account.  You may modify this data at any time by clicking on the &quot;My Account&quot;
link at the top of the page.
{/editable}
<br /><br />
<h3>{editable id="userDataHeader"}User Data:{/editable}</h3>
{editable id="userDataText"}This data is your critical user data.{/editable}<br /><br />
{if $acl.edit || $acl.delete || $acl.changePassword}
    {if $acl.edit}
    <span class="editButton">
        <a href="{$sitePrefix}/account/index/edit/?userId={$userData.userId}">Edit User Data</a>
    </span>
    {/if}
    {if $acl.changePassword}
    <span class="passwordButton">
        <a href="{$sitePrefix}/account/index/change-password/">Change Password</a>
    </span>
    {/if}    
    {if $acl.delete}
    <span class="deleteButton">
        <a href="{$sitePrefix}/account/index/delete/?userId={$userData.userId}">Delete User</a>
    </span>
    {/if}
    <br /><br />
{/if}
<table class="form">
    <tbody>
        <tr>
            <td><label>Username:</label></td>
            <td>{$userData.displayUserId}</td>
        </tr>
        <tr>
            <td><label>Login Method:</label></td>
            <td>{$userData.authAdapter.name}</td>
        </tr>
        <tr>
            <td><label>Access Role:</label></td>
            <td>{$userData.role}</td>
        </tr>
        <tr>
            <td width="95">
            <label>Name:</label></td>
            <td>{$userData.firstName} {$userData.lastName}</td>
        </tr>                 
        <tr>
            <td>            
            <label>Email:</label></td>
            <td>{$userData.emailAddress|empty_alt:"None"}</td>
        </tr> 
        {foreach from=$attributes item=a key=k}
        <tr>
            <td>            
            <label>{$k|capitalize}:</label></td>
            <td>{$a|empty_alt:"None"}</td>
        </tr>         
        {/foreach}                          
    </tbody>
</table> 
{if $remote}
<br />
<h3>{editable id="remoteAccessHeader"}Remote API Access:{/editable}</h3>
{editable id="remoteAccessText"}Remote access will allow you to access this
applications functionality remotely over a SOAP interface.  You will need a
code to access the application.
{/editable}<br /><br />
{if $acl.generateApiCode || $acl.deleteApiCode}
    {if $acl.generateApiCode}
    <span class="addButton">
        <a href="{$sitePrefix}/account/index/generate-api-code/?userId={$userData.userId}">{if $apiCode != ''}Re-{/if}Generate Code</a>
    </span>
    {/if}
    {if $acl.deleteApiCode && $apiCode != ''}
    <span class="deleteButton">
        <a href="{$sitePrefix}/account/index/delete-api-code/?userId={$userData.userId}">Delete Code</a>
    </span>
    {/if}
    <br /><br />
{/if}
<table class="form">
    <tbody>
        <tr>
            <td><label>SOAP API Code:</label></td>
            <td>{$apiCode|empty_alt:"No remote access code found"}
            </td>
        </tr>
    </tbody>
</table>
{/if}
