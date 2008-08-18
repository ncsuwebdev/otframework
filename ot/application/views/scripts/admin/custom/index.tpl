{editable id="customHome"}
This system allows for custom attributes to be added to a certain set of 
objects within the system.  Select the object below to modify any attributes
associated with it. 
{/editable}
<br /><br />

    <table class="list sortable">
    {foreach from=$objects item=o name=objects}
        {if $smarty.foreach.objects.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="200">Object</th>
            <th width="350">Description</th>
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            <td><a href="{$sitePrefix}/admin/custom/details/?objectId={$o.objectId}">{$o.objectId}</a></td>
            <td>{$o.description}</td>
        </tr>
    {foreachelse}
        <tr>
            <td class="noResults">No objects found.</td>
        </tr>
    {/foreach}
    </table>