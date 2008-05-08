<div class="message" id="changedNotice">Your changes will not be permanent until you hit the Save Navigation button</div>

<div id="navEditor" style="display: none;">
    <div id="navEditorHandle">
        <input type="button" id="saveElementButton" value="Store Changes" />
        <input type="button" id="cancelElementButton" value="Cancel" />
        <input type="button" id="deleteElementButton" value="Remove Item" />
    </div>
    <form id="navEditorForm">
    <table>
        <tbody>
        <tr>
            <td><label for="module">Module</label></td>
            <td><input id="moduleBox" name="module" type="text" value="" size="25" /></td>
        </tr>
        <tr>
            <td><label for="controller">Controller</label></td>
            <td><input id="controllerBox" name="controller" type="text" value="" size="25" /></td>
        </tr>
        <tr>
            <td><label for="action">Action</label></td>
            <td><input id="actionBox" name="action" type="text" value="" size="25" /></td>
        </tr>
        <tr>
            <td><label for="display">Display</label></td>
            <td><input id="displayBox" name="display" type="text" value="" size="25" /></td>
        </tr>
        <tr>
            <td><label for="link">Link</label></td>
            <td><input id="linkBox" name="link" type="text" value="" size="25" /></td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        </tbody>
    </table>
    </form>
</div>

<input type="button" id="saveNavButton" value="Save Navigation" />
&nbsp;
<input type="button" id="resetNavButton" value="Reset Navigation" />
&nbsp;
<input type="button" id="addElementButton" value="Add New Item" />
<div id="alert">&nbsp;</div>
<ul id="navMenuContainer">
    {foreach from=$navData.tabs.tab item=t}
        {if $t.submenu}
            <li id="" title='{$t.info}' class="navItem"><span>{$t.display}</span>
                <ul>
                {foreach from=$t.submenu.tab item=st}
                    <li id="" title='{$st.info}' class="subNavItem"><span>{$st.display}</span></li>
                {/foreach}
                </ul>
            </li>
        {else}
            <li id="" title='{$t.info}' class="navItem"><span>{$t.display}</span></li>
        {/if}
    {/foreach}
</ul>