<div id="aclIndexDelete">
    <form method="POST" action="">
        <input type="hidden" name="originalRoleName" value="{$originalRoleName}" />
        You have selected to delete the role <b>{$originalRoleName}</b>.  This will disable
        access to all users who are assigned this role.  You can assign those users
        new roles through the <a href="{$sitePrefix}/user/">User</a> page.
        <br /><br />
        Are you sure you want to do this?
        <br /><br />
        <input type="submit" value="Yes, Delete Role" />
        <input type="button" value="No, Go Back" onclick="history.go(-1);" />
    </form>
</div>