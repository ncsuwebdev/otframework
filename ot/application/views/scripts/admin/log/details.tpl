<div id="adminLogDetails">
    <table class="form">
        <tr>
            <td><label>User ID:</label></td>
            <td><a href="{$sitePrefix}/admin/log/?userId={$log.userId}">{$log.userId}</a></td>
        </tr>
        <tr>
            <td><label>Access Role:</label></td>
            <td><a href="{$sitePrefix}/admin/log/?role={$log.role}">{$log.role}</a></td>
        </tr>
        <tr>
            <td><label>Priority:</label></td>
            <td><a href="{$sitePrefix}/admin/log/?priority={$log.priority}">{$log.priorityName}</a></td>
        </tr>
        <tr>
            <td><label>Message</label></td>
            <td>{$log.message}</td>
        </tr>
        <tr>
            <td><label>Timestamp</label></td>
            <td>{$log.timestamp|date_format:$config.dateTimeFormat}</td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td></td>
        </tr>
        <tr>
            <td><label>Affected Attribute:</label></td>
            <td><a href="{$sitePrefix}/admin/log/?attributeName={$log.attributeName}&amp;attributeId={$log.attributeId}">{$log.attributeName} = {$log.attributeId}</a></td>
        </tr>
        <tr>
            <td><label>Requested Page:</td>
            <td><a href="{$sitePrefix}/admin/log/?request={$log.request}">{$log.request|truncate:90}</a></td>
        </tr>
        <tr>
            <td><label>Session:</label></td>
            <td><a href="{$sitePrefix}/admin/log/?sid={$log.sid}">{$log.sid}</a></td>
        </tr>
    </table>
</div>