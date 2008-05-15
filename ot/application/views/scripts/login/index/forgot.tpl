{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="forgotTitle"}
If you have forgotten your password, enter your username below and we can send a
reset request to the email address we have on file for you.
{/editable}
<br />
<br />
<div id="forgotForm">
{$form}
</div>