{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="userConfig"}
You can edit the application configuration from here. These changes will be made 
globally and immediately.
{/editable}
<br /><br />
<div class="description">
    <h3>Description:</h3>
{$description|empty_alt:"No Description Provided"}
</div>
{$form}
