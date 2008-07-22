{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="loginMessage"}Select the way you would like to log in.{/editable}  
<br /><br />
<div id="formLogin">
{$form}
</div>
<br /><br />