<div class="maintenance {if $maintenanceMode}maintenanceModeOn{else}maintenanceModeOff{/if}">
    {if $maintenanceMode}
        This application is currently in maintenance mode
    {else}
        This application is not in maintenance mode
    {/if}
</div>
<br />
{if $maintenanceMode}
    <a href="{$sitePrefix}/admin/maintenance/toggle?status=off">Turn maintenance mode off</a>
{else}
    <a href="{$sitePrefix}/admin/maintenance/toggle?status=on">Turn maintenance mode on</a>
{/if}