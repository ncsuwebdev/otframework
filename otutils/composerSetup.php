<?php

$otfVendorPath = realpath(dirname(__FILE__) . '/vendor/ncsuwebdev/otframework');
$basePath = realpath(dirname(__FILE__));

$foldersToLink = array(
    '/application/languages/ot',
    '/application/modules/ot',
    '/public/scripts/ot',
    '/public/css/ot',
    '/public/images/ot',
    '/public/themes/ot',
    '/public/min',
    '/otutils',
);

foreach ($foldersToLink as $f) {
    exec('rm ' . $basePath . $f);
    exec('ln -s ' . $otfVendorPath . $f . ' ' . $basePath . $f);
}

$writable = array(
    '/cache',
    '/overrides',
);

foreach ($writable as $w) {
    exec('chmod -R 757 ' . $basePath . $w);
}