<tr>
    <td{if strlen($tempAttribute.label) >= 40} colspan="2"{/if}><label for="{$tempAttribute.attributeId}">{$tempAttribute.label}:</label></td>
    <td>
    {if strlen($tempAttribute.label) < 40}
        {foreach from=$value key=optionIndex item=v}
            {if $tempAttribute.type == 'radio' || $tempAttribute.type == 'select' || $tempAttribute.type == 'checkbox'}
            <b>{$tempAttribute.options.$optionIndex}</b>: {$v}<br />
            {else}
            {$v}<hr>
            {/if}
        {foreachelse}
        No Data Returned
        {/foreach}
    {/if}
    </td>
</tr>
{if strlen($tempAttribute.label) >= 40}
<tr>
    <td colspan="2">
        {foreach from=$value key=optionIndex item=v}
            {if $tempAttribute.type == 'radio' || $tempAttribute.type == 'select' || $tempAttribute.type == 'checkbox'}
            <b>{$tempAttribute.options.optionIndex}</b>: {$v}<br />
            {else}
            {$v}
            {/if}
        {foreachelse}
        No Data Returned
        {/foreach}
    </td>
</tr>
{/if}
