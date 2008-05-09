{$this->doctype('XHTML1_STRICT')}
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
<title>{$config.appTitle} - {$title}</title>
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/Ot/reset-fonts-grids.css" />
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/Ot/base.css" />
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/Ot/common.css" />
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/layout.css" />
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/nav.css" />
{foreach from=$css item=c}
<link rel="stylesheet" type="text/css" media="all" href="{$sitePrefix}/public/css/{$c}" />
{/foreach}
<script type="text/javascript" src="{$sitePrefix}/public/scripts/mootools.v1.11.js"></script>
<script type="text/javascript" src="{$sitePrefix}/public/scripts/cnet/mootools.extended/Native/element.dimensions.js"></script>
<script type="text/javascript" src="{$sitePrefix}/public/scripts/cnet/mootools.extended/Native/element.position.js"></script>
<script type="text/javascript" src="{$sitePrefix}/public/scripts/global.js"></script>
{if $useInlineEditor}
<script type="text/javascript" src="{$sitePrefix}/public/scripts/moo.prompt.v1.js"></script>
<script type="text/javascript" src="{$sitePrefix}/public/scripts/iEdit.js"></script>
{/if}
{if $useInlineEditor || $useTinyMce}
<script type="text/javascript" src="{$sitePrefix}/public/scripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript" src="{$sitePrefix}/public/scripts/tinyMceConfig.js"></script>
{/if}
{foreach from=$javascript item=script}
<script type="text/javascript" src="{$sitePrefix}/public/scripts/{$script}"></script>
{/foreach}
</head>
<body>
    <input type="hidden" name="sitePrefix" id="sitePrefix" value="{$sitePrefix}" />
    
    <div id="custom-doc" class="yui-t7"> 
        <div id="hd">
            <div id="authStatus">
                {layout section=auth}
            </div>
        </div> 
        <div id="bd"> 
            <div class="yui-g" id="content"> 
                {layout section=nav}
                <br /><br />
                <h2><span>{$title}</span></h2>
                {layout section=content} 
            </div>    
        </div> 
        <div id="ft">
             {layout section=footer}
        </div> 
    </div>
</body>
</html>