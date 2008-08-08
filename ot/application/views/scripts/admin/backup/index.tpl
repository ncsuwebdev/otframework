<p>Select the table you would like to download as a CSV.</p>

<form method="post" action="{$sitePrefix}/admin/backup/get-backup">
	{html_options options=$tables name="tableName"}
	<input type="submit" value="Get Backup" />
</form>