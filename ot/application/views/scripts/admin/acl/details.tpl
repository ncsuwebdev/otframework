<div id="aclIndexDetails">
    These are the individual details for the {$role.name} access role as they appear
    in the actual ACL configuration file.<br /><br />
    {if $acl.edit && $role.editable}
        <span class="editButton"><a href="{$sitePrefix}/admin/acl/edit/?originalRoleName={$role.name}">Edit</a></span>
    {/if}
    {if $acl.delete && $role.editable}
        <span class="deleteButton"><a href="{$sitePrefix}/admin/acl/delete/?originalRoleName={$role.name}">Delete</a></span>
    {/if}
    <br /><br />
    <table class="form">
        <tr>
            <td><label>Role Name:</label></td>
            <td>{$role.name}</td>
        </tr>
        <tr>
            <td><label>Inherit From:</label></td>
            <td>{$role.inherit}</td>
        </tr>
        <tr>
            <td><label>Editable?</label></td>
            <td>{if $role.editable == 1}Yes{else}No{/if}</td>
        </tr>
        <tr>
            <td><label>Allows:</label></td>
            <td>
            {foreach from=$role.allows item=allow}
            Allow {$allow.privilege} for {$allow.resource}<br />
            {foreachelse}
            None Specified
            {/foreach}
            </td>
        </tr>
        <tr>
            <td><label>Denys:</label></td>
            <td>
            {foreach from=$role.denys item=deny}
            Deny {$deny.privilege} for {$deny.resource}<br />
            {foreachelse}
            None Specified
            {/foreach}
            </td>
        </tr>
    </table>

</div>