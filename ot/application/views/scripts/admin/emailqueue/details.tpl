<div id="adminemailDetails">
    <table class="form">
        <tr>
            <td><label>Status:</label></td>
            <td><a href="{$sitePrefix}/admin/emailqueue/?status={$email.status}">{$email.status|capitalize}</a></td>
        </tr>
        <tr>
            <td><label>Queue Date:</label></td>
            <td>{$email.queueDt|date_format:$config.dateTimeFormat}</td>
        </tr>
        <tr>
            <td><label>Sent Date:</label></td>
            <td>{if $email.status eq 'sent'}{$email.sentDt|date_format:$config.dateTimeFormat}{else}Not Sent{/if}</td>
        </tr>
        <tr>
            <td><label>Affected Attribute:</label></td>
            <td><a href="{$sitePrefix}/admin/emailqueue/?attributeName={$email.attributeName}&amp;attributeId={$email.attributeId}">{$email.attributeName} = {$email.attributeId}</a></td>
        </tr>
        <tr>
            <td><label>Call ID:</label></td>
            <td><a href="{$sitePrefix}/admin/emailqueue/?callId={$email.callId}">{$email.callId|empty_alt:None}</a></td>
        </tr>
    </table>
    <br /><br />
    <b>Message:</b><br /><br />
    <table class="form">
        <tr>
            <td><label>To:</label></td>
            <td>{$email.msg.to}</td>
        </tr>
        <tr>
            <td><label>From:</label></td>
            <td>{$email.msg.from}</td>
        </tr>
        <tr>
            <td><label>Subject:</td>
            <td>{$email.msg.subject}</td>
        </tr>
        <tr>
            <td><label>Body:</label></td>
            <td>{$email.msg.body}</td>
        </tr>
    </table>
</div>