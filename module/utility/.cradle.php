<?php //-->
$cradle->preprocess(function() {
    $extensions = $this->package('global')->path('public') . '/json/extensions.json';
    $json = file_get_contents($extensions);
    Cradle\Module\Utility\File::$extensions = json_decode($json, true);
});
