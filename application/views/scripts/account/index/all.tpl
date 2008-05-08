{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
<div id="userIndexIndex">
    {if $acl.add}
    <a href="{$sitePrefix}/account/index/add/"><img src="{$sitePrefix}/public/images/add.png" alt="Add Account"></a>
    <a href="{$sitePrefix}/account/index/add/">Add New Account</a><br /><br />
    {/if}
    {editable id="user"}
    Here you can add users and grant them roles.  Roles are defined by the applicaiton
    administrator and can be set <a href="{$sitePrefix}/admin/acl/">here</a>.
    {/editable}
    <br /><br />
    <table class="list sortable">
    {foreach from=$users item=u name=users}
        {if $smarty.foreach.users.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="80">Username</th>
            <th width="150">Login Method</th>
            <th width="200">Access Role</th>
            {if $acl.edit}
            <th width="50">Edit</th>
            {/if}
            {if $acl.delete}
            <th width="50">Delete</th>
            {/if}
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            {assign var='realm' value=$u.userId|regex_replace:"/^[^@]*@/":""}
            <td><a href="{$sitePrefix}/account/?userId={$u.userId}">{$u.userId|regex_replace:"/@.*$/":""}</a></td>
            <td align="center">{$realms.$realm.name}</td>
            <td align="center">{$u.role}</td>
            {if $acl.edit}
            <td align="center">
                <a href="{$sitePrefix}/account/index/edit/?userId={$u.userId}"><img src="{$sitePrefix}/public/images/edit.png" alt="Edit {$u.userId}" /></a>
            </td>
            {/if}
            {if $acl.delete}
            <td align="center">
                {if $u.userId != $loggedInUserId}
                <a href="{$sitePrefix}/account/index/delete/?userId={$u.userId}"><img src="{$sitePrefix}/public/images/delete.png" alt="Delete {$u.userId}" /></a>
                {else}
                <img src="{$sitePrefix}/public/images/deleteDisabled.png" alt="Delete Disabled for {$u.userId}" />
                {/if}
            </td>
            {/if}
        </tr>
    {foreachelse}
        <tr>
            <td class="noResults">No Users found</td>
        </tr>
    {/foreach}
    </table>
</div>