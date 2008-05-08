<div class="message" id="alertText" style="visibility: hidden;">&nbsp;</div>

<div id="replacementsDiv">
    <form id="customTextForm">
    <input type="hidden" name="path" id="path" value="" />
    <input type="button" id="customTextSaveButton" value="Save Changes" />
    <br /><br />
    <div id="replacementsContent"></div>
    </form>
</div>

<p>
    <input type="button" id="treeExpandButton" value="Expand All" />
    <input type="button" id="treeCollapseButton" value="Collapse All" />
</p>

<div id="fileTreeWrapper">
    <div id="fileTree"></div>
</div>

<ul id="fileTreeData">
    {foreach from=$files item=module key=module_key}
        {if is_array($module)}
            <li><a>{$module_key}</a>
            <ul>
            {foreach from=$module item=controller key=controller_key}
                {if is_array($controller)}
                    <li><a>{$controller_key}</a>
                    <ul>
                    {foreach from=$controller item=action key=action_key}
                        <li id="{$module_key}/{$controller_key}/{$action_key}"><a href="{$module_key}/{$controller_key}/{$action_key}" target="file"><!-- icon:_doc; -->{$action_key}</a></li>
                    {/foreach}
                    </ul>
                {else}
                    <li><a>{$controller_key}</a></li>
                {/if}
            {/foreach}
            </ul>
        {else}
            <li><a href="{$module_key}" target="file"><!-- icon:_doc; -->{$module_key}</a></li>
        {/if}
    {/foreach}
</ul>
<div style="clear: both;"></div>