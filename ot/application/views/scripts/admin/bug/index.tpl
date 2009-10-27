<div id="adminSemesterIndex">
    These are the bugs which have been filed.<br /><br />

    {if $acl.add}
    <span class="addButton">
        <a href="{$sitePrefix}/admin/bug/add/">New Bug Report</a>
    </span>
    <br /><br />
    {/if}
    <table class="list sortable">
    {foreach from=$bugs item=b name=bugs}
        {if $smarty.foreach.bugs.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="300">Bug Title</th>
            <th width="200">Submit Date/Time</th>
            <th width="100">Status</th>
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            <td>
            {if $acl.details}
                <a href="{$sitePrefix}/admin/bug/details/?bugId={$b.bugId}">{$b.title}</a>
            {else}
                {$b.title}
            {/if}
            </td>
            <td style="text-align:center">{$b.submitDt|date_format:$config.dateTimeFormat}</td>
            <td style="text-align:center">{$b.status|capitalize}</td>
        </tr>
    {foreachelse}
        <tr>
            <td class="noResults">No Bugs found</td>
        </tr>
    {/foreach}
    </table>
</div>