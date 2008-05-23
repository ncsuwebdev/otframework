<div id="menu" align="right">
    <div id="linksmenu" align="center">
    {foreach from=$nav item=t name=tabs}
        <div>
        {if $t.link != ''}
            <a href="{$t.link}" target="{$t.target}">{$t.display}</a>
        {else}
            {$t.display}
        {/if}
        {if count($t.sub) != 0}
            <div class="sub_menu" id="menu_{$smarty.foreach.tabs.index}">
            {foreach from=$t.sub item=s}
                <a href="{$s.link}" target="{$s.target}">{$s.display}</a>
            {/foreach}
            </div>
        {/if}
        </div>
    {/foreach}
    </div>
</div>