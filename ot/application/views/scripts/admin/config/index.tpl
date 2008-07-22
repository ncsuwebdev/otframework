{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="appConfigEdit"}This is the current configuration of the application.{/editable}
<br /><br />    
<table class="list sortable">
    <tbody>
        <tr>
            <th width="250">Name</th>
            <th width="350">Value</th>
            {if $acl.edit}
            <th width="50">Edit</th>
            {/if}
        </tr>
        {foreach from=$configList item=c}
        <tr>
            <td class="description" title="{$c.description|empty_alt:"No Description Provided"}">
                <img src="{$sitePrefix}/public/ot/images/help.png" class="floatRight" width="16" height="16" />
                {$c.key}
            </td>
            <td>{$c.value}</td>
            {if $acl.edit}
            <td style="text-align: center">
            <a href="{$sitePrefix}/admin/config/edit/?key={$c.key}"><img src="{$sitePrefix}/public/ot/images/edit.png" alt="Edit option for {$c.key}"></a>
            </td>
            {/if}
        </tr>
        {/foreach}
    </tbody>
</table>