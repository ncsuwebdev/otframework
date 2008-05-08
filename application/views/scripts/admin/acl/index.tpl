<div id="aclIndexIndex">
    Access Roles provide a simple approach to managing user access within the application.
    Roles are created by any user with access, allowing them to grand and revoke access to certain
    resources within the application.  Resources are defined as accessible functions
    within the application.<br /><br />

    Below are all active roles that are available to be assigned to users:<br /><br />

    {if $acl.add}
    <a href="{$sitePrefix}/admin/acl/add/"><img src="{$sitePrefix}/public/images/add.png" alt="Add Access Role"></a>
    <a href="{$sitePrefix}/admin/acl/add/">Add New Access Role</a><br /><br />
    {/if}
    <table class="list sortable">
    {foreach from=$roles item=r name=roles}
        {if $smarty.foreach.roles.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="200">Role Name</th>
            <th width="200">Inherited From</th>
            {if $acl.edit}
            <th width="50">Edit</th>
            {/if}
            {if $acl.delete}
            <th width="50">Delete</th>
            {/if}
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            <td><a href="{$sitePrefix}/admin/acl/details/?originalRoleName={$r.name}">{$r.name}</a></td>
            <td align="center">{$r.inherit}</td>
            {if $acl.edit}
            <td align="center">
                {if $r.editable}
                <a href="{$sitePrefix}/admin/acl/edit/?originalRoleName={$r.name}"><img src="{$sitePrefix}/public/images/edit.png" alt="Edit {$r.name}" /></a>
                {else}
                <img src="{$sitePrefix}/public/images/editDisabled.png" alt="Edit Disabled for {$r.name}" />
                {/if}
            </td>
            {/if}
            {if $acl.delete}
            <td align="center">
                {if $r.editable}
                <a href="{$sitePrefix}/admin/acl/delete/?originalRoleName={$r.name}"><img src="{$sitePrefix}/public/images/delete.png" alt="Delete {$r.name}" /></a>
                {else}
                <img src="{$sitePrefix}/public/images/deleteDisabled.png" alt="Delete Disabled for {$r.name}" />
                {/if}
            </td>
            {/if}
        </tr>
        {foreachelse}
        <tr>
            <td class="noResults">No Roles found</td>
        </tr>
        {/foreach}
    </table>
</div>