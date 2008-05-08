<div id="adminLogIndex">
    The following are the action logs recorded.  Enter any search criteria below:<br /><br />

    <form method="get" action="">
        <table class="form">
            <tr>
                <td><label for="userId">User ID:</label></td>
                <td><input type="text" name="userId" id="userId" value="{$userId}" /></td>
            </tr>
            <tr>
                <td><label for="role">Access Role:</label></td>
                <td><input type="text" name="role" id="role" value="{$role}" /></td>
            </tr>
            <tr>
                <td><label>Date Range:</label></td>
                <td>
                    <input type="text" name="beginDt" id="beginDt" value="{$beginDt}" />
                    <b>- to -</b>
                    <input type="text" name="endDt" id="endDt" value="{$endDt}" />
                </td>
            </tr>
            <tr>
                <td><label>Attribute:</label></td>
                <td>
                    <input type="text" name="attributeName" id="attributeName" value="{$attributeName}" />
                    <input type="text" name="attributeId" id="attributeId" value="{$attributeId}" />
                </td>
            </tr>
            <tr>
                <td><label for="request">Request:</td>
                <td><input type="text" name="request" id="request" value="{$request}" /></td>
            </tr>
            <tr>
                <td><label for="sid">Session:</label></td>
                <td><input type="text" name="sid" id="sid" value="{$sid}" /></td>
            </tr>
            <tr>
                <td><label for="priority">Priority:</label></td>
                <td>{html_options name=priority id=priority options=$priorityTypes selected=$priority}</td>
            </tr>
        </table>
        <input type="submit" value="Filter Results" />
    </form>
    <br /><br />
    <table class="list sortable">
    {foreach from=$logs item=l name=logs}
        {if $smarty.foreach.logs.index % $config.headerRowRepeat == 0}
        <tr>
            <th width="60">User ID</th>
            <th width="80">Access Role</th>
            <th width="300">Message</th>
            <th width="150">Request</th>
            <th width="120">Timestamp</th>
            <th width="50">Details</th>
        </tr>
        {/if}
        <tr class="{cycle values="row1,row2"}">
            <td style="text-align: center">{$l.userId}</td>
            <td style="text-align: center">{$l.role}</td>
            <td>{$l.message|truncate:40}</td>
            <td>{$l.request|truncate:40}</td>
            <td>{$l.timestamp|date_format:$config.dateTimeFormat}</td>
            <td style="text-align: center">
                <a href="{$sitePrefix}/admin/log/details/?logId={$l.logId}"><img src="{$sitePrefix}/public/images/details.png" alt="Details" /></a>
            </td>
        </tr>
        {foreachelse}
        <tr>
            <td class="noResults">No Logs found</td>
        </tr>
        {/foreach}
    </table>
</div>