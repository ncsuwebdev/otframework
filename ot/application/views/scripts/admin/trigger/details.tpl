<a href="{$sitePrefix}/admin/trigger/">&lt;&lt; Back to Triggers</a><br /><br />
{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="triggerDetails"}
You can manage the actions for this trigger here.
{/editable}
<br /><br />
<table class="form">
    <tbody>
        <tr>
            <td><label>Trigger:</label></td>
            <td>{$triggerId}</td>
        </tr>
        <tr>
            <td><label>Description:</label></td>
            <td>{$triggerDescription}</td>
        </tr>
    </tbody>
</table><br />
    {if $acl.add}
    <span class="addButton">
        <a href="{$sitePrefix}/admin/trigger/add/?triggerId={$triggerId}">Add New Action</a>
    </span><br /><br />
    {/if}
    
    
    <table class="list sortable">
        <tbody>
            <tr>
                <th width="200">Name</th>
                <th width="300">Action</th>
                {if $acl.edit}
                <th width="50">Edit</th>
                {/if}
                {if $acl.delete}
                <th width="50">Delete</th>
                {/if}
            </tr>
            {foreach from=$actions item=a}
            <tr>
                <td>{$a.name}</td>
                <td>{$a.helper}</td>
                {if $acl.edit}
                <td align="center"><a href="{$sitePrefix}/admin/trigger/edit/?triggerActionId={$a.triggerActionId}"><img src="{$sitePrefix}/public/ot/images/edit.png" alt="Edit Action"></a></td>
                {/if}
                {if $acl.delete}
                <td align="center"><a href="{$sitePrefix}/admin/trigger/delete/?triggerActionId={$a.triggerActionId}"><img src="{$sitePrefix}/public/ot/images/delete.png" alt="Delete Action"></a></td>
                {/if}
            </tr>
            {foreachelse}
            <tr>
                <td class="noResults" colspan="4">No Actions Found</td>
            </tr>
            {/foreach}
        </tbody>
    </table>