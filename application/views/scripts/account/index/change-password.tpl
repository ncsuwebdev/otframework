{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="changePassword"}You may change your own password by filling in the fields below. 
If this is the first time logging into the system, you must change your password
to proceed.<br /><br />
Fill in the following fields to change your password.
{/editable}
<br /><br />
<div id="changeForm">
{$form}
</div>