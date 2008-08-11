This interface provides the ability to manage cron jobs for {$config.appTitle}.<br /><br />

    {if $acl.add}
    <a href="{$sitePrefix}/admin/cron/add/"><img src="{$sitePrefix}/public/images/add.png" alt="Add Cron Job"></a>
    <a href="{$sitePrefix}/admin/cron/add/">Add Cron Job</a>
    {/if}

    {if $acl.toggle}
    <span class="enableAllButton">
        <a href="{$sitePrefix}/admin/cron/toggle/?path=all&status=disabled">Enable All</a>
    </span>
    <span class="disableAllButton">
        <a href="{$sitePrefix}/admin/cron/toggle/?path=all&status=enabled">Disable All</a>
    </span>
    <br /><br />
    {/if}
    <table class="list sortable">
    {foreach from=$cronjobs item=c name=cronjobs}
        {if $smarty.foreach.cronjobs.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="250">Cron Job</th>
            <th width="130">Status</th>
            {if $acl.edit}
            <th width="50">Edit</th>
            {/if}
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            <td>{$c.path}</td>
            <td style="text-align:center" class="{$c.status}">
            {if $acl.toggle}
            <a href="{$sitePrefix}/admin/cron/toggle/?path={$c.path}">
            {/if}
            {if $c.status == 'enabled'}
            Enabled
            {else}
            Disabled
            {/if}
            {if $acl.toggle}
            </a>
            {/if}
            </td>
            {if $acl.edit}
            <td style="text-align:center">
                <a href="{$sitePrefix}/admin/cron/edit/?path={$c.path}"><img src="{$sitePrefix}/public/images/edit.png" alt="Edit" /></a>
            </td>
            {/if}
        </tr>
    {foreachelse}
        <tr>
            <td class="noResults">No Cron Jobs found</td>
        </tr>
    {/foreach}
    </table>