{editable id="triggerHome"}
Triggers are actions that happen with the applications.  By adding a trigger
action, you can have the application execute some code at a designated point
of the application.<br /><br />
These are all the available triggers in the system.    
{/editable}
<br /><br />
    
    <table class="list sortable">
        <tbody>
            <tr>
                <th width="250">Name</th>
                <th width="350">Description</th>
            </tr>
            {foreach from=$triggers item=t}
            <tr>
                <td>
                    {if $acl.details}
                    <a href="{$sitePrefix}/admin/trigger/details/?triggerId={$t.name}">
                    {/if}
                    {$t.name}
                    {if $acl.details}
                    </a>
                    {/if}
                </td>
                <td>{$t.description}</td>                
            </tr>
            {/foreach}
        </tbody>
    </table>