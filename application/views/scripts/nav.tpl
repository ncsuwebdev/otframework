<div id="topmenu">
    <ul>
    {foreach from=$nav item=t name=tabs}
        <li>
        {if $t.link != ''}
             <a href="{$t.link}" target="{$t.target}"><span>{$t.display}</span></a>
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
        </li>
    {/foreach}
    </ul>
</div>