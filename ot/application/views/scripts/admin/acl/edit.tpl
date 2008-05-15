<div id="aclIndexAdd">
    Select the name that you would like to call the role.  This system also allows
    roles to inherit permission from an existing role.  Inhertance is optional
    but is useful in implementing a tiered access system.<br /><br />

    <form method="post" id="aclEditor" action="{$sitePrefix}/admin/acl/{$action}" class="checkRequiredFields">
        <input type="hidden" name="originalRoleName" id="originalRoleName" value="{$originalRoleName}" />
        <table class="form">
            <tr>
                <td><label for="roleName">Role Name:</label></td>
                <td><input type="text" class="required" name="roleName" id="roleName" value="{$roleName}" size="30" maxlength="126" /></td>
            </tr>
            <tr>
                <td><label for="role">Inherit From:</label></td>
                <td>{html_options name=inheritRoleName id=inheritRoleName class=required options=$roles selected=$inheritRoleName}
                <input type="button" value="Pre-Populate" onclick="if (confirm('You will lose any changes you have made.')) location.href='{$sitePrefix}/admin/acl/{$action}/?roleName=' + document.getElementById('roleName').value + '&originalRoleName=' + document.getElementById('originalRoleName').value + '&inheritRoleName=' + document.getElementById('inheritRoleName').value; return false;" /></td>
            </tr>
        </table><br /><br />

        {if $action == 'edit' && count($children) != 0}
        <table class="form highlight">
            <tr>
                <td><b>CAUTION!</b><br /><br />
                Making changes to this role will affect the following roles which are
                inherited (directly or indirectly) from this role:
                <ul>
                {foreach from=$children item=c}
                <li><a href="{$sitePrefix}/admin/acl/details/?originalRoleName={$c.name}">{$c.from}</li>
                {/foreach}
                </ul>
                </td>
            </tr>
        </table><br /><Br />
        {/if}
        <div id="accessList">
            {foreach from=$resources key=module item=controllers}
            <div class="aclSection">
                <table class="list">
                    <tr class="module">
                        <td width="300"><b>{$module|capitalize}</b></td>
                        <td width="120">Currently:</td>
                        <td width="150">Grant/Revoke:</td>
                    </tr>
{foreach from=$controllers key=controller item=actions}
                    <tr class="controller">
                        <td title="{$actions.description}" class="td1 description">
                        <img src="{$sitePrefix}/public/images/help.png" class="info" width="16" height="16" />
                        {$controller|capitalize}
                        </td>
                        <td class="{if $actions.all.access}access{else}{if $actions.someaccess}someAccess{else}noAccess{/if}{/if}">
                        {if $actions.all.access}
                            All Access
                        {else}
                            {if $actions.someaccess}
                            Some Access
                            {else}
                            No Access
                            {/if}
                        {/if}
                        </td>
                        <td>
                        <select size="1" class="allAccess" name="{$module}[{$controller}][all]" id="{$module}_{$controller}">
                            <option value="{if $actions.all.access}allow{else}{if $actions.someaccess}some{else}deny{/if}{/if}">No Change</option>
                        {if !$actions.all.access}
                            <option value="allow">Grant All Access</option>
                        {/if}       
                        {if !$actions.someaccess || $actions.all.access}                 
                            <option value="some">{if !$actions.all.access}Grant{else}Revoke{/if} Some Access</option>
                        {/if}
                        {if $actions.all.access || $actions.someaccess}
                            <option value="deny">Revoke All Access</option>
                        {/if}
                        </select>                        
                        </td>
                    </tr>
                        {foreach from=$actions.part key=action item=access}
                    <tr class="action {$module}_{$controller}" style="display:{if !$actions.someaccess || $actions.all.access}none{/if}">
                        <td class="td1 description" title="{$access.description}">
                        <img src="{$sitePrefix}/public/images/help.png" width="16" height="16" class="info" />
                        {$action|capitalize}
                        </td>
                        <td class="{if $access.access}access{else}noAccess{/if}">{if $access.access}Has Access{else}No Access{/if}</td>
                        <td class="td3"><input type="checkbox" class="{$module}_{$controller}_action" value="{if $access.access}deny{else}allow{/if}" name="{$module}[{$controller}][part][{$action}]" id="{$module}_{$controller}_part_{$action}" /> {if $access.access}Revoke Access{else}Grant Access{/if}</td>
                    </tr>
                        {/foreach}
                    {/foreach}
                </table>
            </div>
            {/foreach}

        </div>
        <input type="submit" value="Set Permission" />
        <input type="button" value="Cancel" onclick="history.go(-1);" />
    </form>
</div>