{editable id="editBug"}Make any changes you would like to the bug.{/editable}
<br /><br />
{$form}
<br /><br />
<h2>Existing Bug Text:</h2>
{foreach from=$text item=t}
    <div class="bugText">
        <div class="header">
            Submitted by {$t.userId|userid} on {$t.postDt|date_format:$config.dateTimeFormat}
        </div>
        <div class="bugContent">
           {$t.text|nl2br}
        </div>
    </div>
{/foreach}