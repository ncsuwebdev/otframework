{if !$maintenanceMode}
<div class="maintenance maintenanceModeOff">
    This application is not in maintenance mode
</div>
{/if}
<br />
{if $maintenanceMode}
    <a href="{$sitePrefix}/admin/maintenance/toggle?status=off">Turn maintenance mode off</a>
{else}
    <a href="{$sitePrefix}/admin/maintenance/toggle?status=on">Turn maintenance mode on</a>
{/if}