<div id="menu" align="right">
    <!-- 
    <div align="right" id="topShadow" style="width: 189px; height: 8px;">
        <img src="{$sitePrefix}/public/images/mnu_topshadow.gif" width="189" height="8" alt="mnutopshadow" />
    </div>
    -->
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
    <!-- 
    <div align="right" id="bottomShadow">
        <img src="{$sitePrefix}/public/images/mnu_bottomshadow.gif" width="189"	height="8" alt="mnubottomshadow" />
    </div>
    -->
</div>