<div class="error">
<ul>
{if $message == ''}
    <li>No error message passed</li>
{else}
    <li>{$message}</li>
{/if}
</ul>
<br />
{if $showTrackback}
<div id="trackback">
    <table class="list">
        <tbody>
            <tr>
                <th width="500">File</th>
                <th width="150">Function</th>
                <th width="50">Line</th>
            </tr>
{foreach from=$trackback item=t}
            <tr>
                <td>{$t.file}</td>
                <td>{$t.function}</td>
                <td>{$t.line}</td>
            </tr>
{/foreach}            
        </tbody>
    </table>
    <br />
</div>
{/if}
<form id="groupForm">
    <input type="button" value="Back" onclick="history.go(-1);" />
</form>
</div>
<span id="error"></span>