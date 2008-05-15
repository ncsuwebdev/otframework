{if count($messages) != 0}
<div class="messageContainer">
    <div class="message">
    {foreach from=$messages item=m}
    {$m}<br />
    {/foreach}
    </div>
</div>
{/if}
{editable id="addBug"}
If you are experiencing an issue with this application, you can file a bug report here.  If you
have a feature request, you should contact your administrator directly.
{/editable}
<br /><br />
{$form}
