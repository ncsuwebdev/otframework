<div>
    {if $acl.edit || $acl.delete}
        {if $acl.edit}
	    <span class="editButton">
	       <a href="{$sitePrefix}/admin/custom/edit/?attributeId={$attribute.attributeId}">Edit</a>
	    </span>
	    {/if}
	    {if $acl.delete}
	    <span class="deleteButton">
	       <a href="{$sitePrefix}/admin/custom/delete/?attributeId={$attribute.attributeId}">Delete</a>
	    </span>
	    {/if}
	    <br /><br />
	{/if}
    <table class="form">
        <tr>
            <td width="100"><label>Object:</label></td>
            <td>{$objectId}</td>
        </tr>
        <tr>
            <td><label>Description:</label></td>
            <td>{$objectDescription}</td>
        </tr>
    </table><br /><br />
    <table class="form">
        <tr>
            <td width="130"><label>Label:</label></td>
            <td>{$attribute.label}</td>
        </tr>
        <tr>
            <td><label>Type:</label></td>
            <td>{$attribute.type}</td>
        </tr>
        <tr>
            <td><label>Required:</label></td>
            <td>{if $attribute.required}Yes{else}No{/if}</td>
        </tr>
        <tr>
            <td><label>Display Direction:</label></td>
            <td>{$attribute.direction|capitalize}</td>
        </tr>  
        {if $attribute.type == 'radio' || $attribute.type == 'select'}   
        <tr>
            <td><label>Options:</label></td>
            <td>
            {foreach from=$attribute.options item=option}
            {$option}<br />
            {foreachelse}
            No Options
            {/foreach}
            </td>
        </tr>                 
        {/if}
    </table>
</div>