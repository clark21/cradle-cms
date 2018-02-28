<?php // -->

return [
    'issuer'    => 'http://cms.cradle.test',
    'audience'  => 'http://cms.cradle.test',
    'secret'    => 'cradle-cms-secret-key',
    'whitelist' => [
        '/rest/system/auth'
    ]
];