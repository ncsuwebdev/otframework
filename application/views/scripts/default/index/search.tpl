{foreach from=$workshops item=w}
    {assign var=wc value=$w->workshopCategoryId}
    <div class="searchResult" style="background-image:url({$sitePrefix}/index/image/?imageId={$categories.$wc.smallIconImageId});">
	    <div class="title"><a href="{$sitePrefix}/workshop/index/details/?workshopId={$w->workshopId}">{$w->title}</a></div>
	    <div class="cescription">{$w->description|strip_tags|truncate:250}</div>
	</div>
{/foreach}