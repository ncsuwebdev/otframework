{editable id="bugDetails"}These are the details provided about the bug.{/editable}
<br /><br />
{if $acl.edit}
  <span class="editButton">
    <a href="{$sitePrefix}/admin/bug/edit/?bugId={$bug.bugId}">Edit Bug</a>
  </span><br /><br />
{/if}
    <table class="form">
        <tr>
            <td><label>Status:</label></td>
            <td>{$bug.status|capitalize}</td>
        </tr>
        <tr>
            <td><label>Submit Date:</label></td>
            <td>{$bug.submitDt|date_format:$config.dateTimeFormat}</td>
        </tr>
        <tr>
            <td><label>Reproducibility:</label></td>
            <td>{$bug.reproducibility|capitalize}</td>
        </tr>
        <tr>
            <td><label>Severity:</label></td>
            <td>{$bug.severity|capitalize}</td>
        </tr>
        <tr>
            <td><label>Priority:</label></td>
            <td>{$bug.priority|capitalize}</td>
        </tr>
        <tr>
            <td><label>Bug Text:</label></td>
            <td>{foreach from=$text item=t}
            <div class="bugText">
                <div class="header">
                    Submitted by {$t.userId|userid} on {$t.postDt|date_format:$config.dateTimeFormat}
                </div>
                <div class="bugContent">
                    {$t.text|nl2br}
                </div>
            </div>
            {/foreach}
            </td>
        </tr>
    </table>