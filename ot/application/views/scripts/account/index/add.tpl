{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="addUser"}Enter in the information below to add a user to the system.{/editable}
<br /><br />           
<div id="addForm">
{$form}
</div>
