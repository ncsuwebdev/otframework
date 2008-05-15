<div>
    {editable id="addCustomAttribute"}Enter in the information below to add custom attribute to this object.{/editable}
    <br /><br />
    <form method="post" action="?objectId={$objectId}" id="add" class="checkRequiredFields">
	   <table class="form">
	        <tr>
	            <td><label>Object:</label></td>
	            <td>{$objectId}</td>
	        </tr>
	        <tr>
	            <td><label>Description:</label></td>
	            <td>{$objectDescription}</td>
	        </tr>
	    </table><br /><br />
	    <table class="form">
	        <tr>
	            <td><label>Label:</label></td>
	            <td><input type="text" name="label" id="label" value="" size="20" class="required" /></td>
	        </tr>
	        <tr>
	            <td><label>Type:</label></td>
	            <td>{html_options options=$types name=type class=required id=type}
	            <div id="opt" style="display: none;">
	                <table class="form" id="options">
		                <tr id="optionRow">
		                    <td width="50"><label for="option[]">Option:</label></td>
		                    <td width="120"><input type="text" maxlength="128" size="20" name="option[]" value="" /></td>
		                </tr>
		            </table>
		            <span class="addButton"><a href="javascript:addNewRow('options', 'optionRow')">Add Row</a></span>
		            &nbsp;&nbsp;
		            <span class="deleteButton"><a href="javascript:removeRow('options')">Remove Row</a></span>
	            </div>
	            </td>
	        </tr>
	        <tr>
	            <td><label>Required:</label></td>
	            <td><input type="checkbox" value="1" name="required" id="required" /></td>
	        </tr>
            <tr>
                <td><label>Display Direction:</label></td>
                <td>
                    <label for="direction"><input type="radio" value="vertical" checked="checked" name="direction" />Vertical</label>
                    <br />
                    <label for="direction"><input type="radio" value="horizontal" name="direction" />Horizontal</label>
                </td>
            </tr>  
	    </table>
        <input type="submit" value="Add Custom Attribute" />
        <input type="button" value="Cancel" onclick="history.go(-1);" />

    </form>
</div>