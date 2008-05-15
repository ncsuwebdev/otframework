
    {if $acl.add}
    <span class="addButton">
        <a href="{$sitePrefix}/admin/custom/add/?objectId={$objectId}">Add New Custom Attribute for {$objectId}</a>
    </span><br /><br />
    {/if}
    <table class="form">
        <tr>
            <td><label>Object:</label></td>
            <td>{$objectId}</td>
        </tr>
        <tr>
            <td><label>Description:</label></td>
            <td>{$objectDescription}</td>
        </tr>
    </table>
    <div id="customAttributeOrder">
		<span id="listStatus">&nbsp;</span><br /><br />
		<span style="display: none;" id="parentIdName">objectId</span>
		<span style="display: none;" id="parentIdValue">{$objectId}</span>
		<span style="display: none;" id="sortUrl">{$sitePrefix}/admin/custom/order-attributes/</span>
		<div id="list">
		{foreach from=$attributes item=a name=attributes}
		<table class="elm" id="{$a.attributeId}">
		    <tbody>
		    <tr>
		        <td class="order">{$smarty.foreach.attributes.iteration}</td>
		        <td class="description">
		            <div>
		                {if $acl.attributeDetails}
		                <a href="{$sitePrefix}/admin/custom/attribute-details/?attributeId={$a.attributeId}">{$a.label}</a>
		                {else}
		                {$a.label}
		                {/if}
		                ({$a.type})
		            </div>
		        </td>
		        <td class="action">
                    {if $acl.edit}
                    <a href="{$sitePrefix}/admin/custom/edit/?attributeId={$a.attributeId}"><img src="{$sitePrefix}/public/ot/images/edit.png" alt="edit" height="16" width"16" /></a>
                    {/if} 		        
		            {if $acl.delete}
		            <a href="{$sitePrefix}/admin/custom/delete/?attributeId={$a.attributeId}"><img src="{$sitePrefix}/public/ot/images/delete.png" alt="delete" height="16" width"16" /></a>
		            {/if} 
		            
		        </td>
		    </tr>
		    </tbody>
		</table>
		{foreachelse}
		No custom attributes found for this object
		{/foreach}
		</div>
    </div>