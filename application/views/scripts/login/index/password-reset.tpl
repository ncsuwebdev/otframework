{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="signup"}
You can reset your password to whatever you like.
{/editable}
<br /><br />
<div id="signupForm">
    {$form}
</div>