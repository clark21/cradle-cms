<?php //-->
include_once __DIR__ . '/src/controller/admin.php';
include_once __DIR__ . '/src/events.php';

use Cradle\Module\History\Service as HistoryService;
use Cradle\Module\System\Utility\ServiceFactory;

use Cradle\Http\Request;
use Cradle\Http\Response;

ServiceFactory::register('history', HistoryService::class);

$cradle->preprocess(function($request, $response) {
    //add helpers
    $handlebars = cradle('global')->handlebars();

    $this->package('/module/history')
    /**
     * Add Template Builder
     *
     */
    ->addMethod('template', function ($file, array $data = [], $partials = []) {
        // get the root directory
        $root = __DIR__ . '/src/template/';

        // check for partials
        if (!is_array($partials)) {
            $partials = [$partials];
        }

        $paths = [];

        foreach ($partials as $partial) {
            //Sample: product_comment => product/_comment
            //Sample: flash => _flash
            $path = str_replace('_', '/', $partial);
            $last = strrpos($path, '/');

            if($last !== false) {
                $path = substr_replace($path, '/_', $last, 1);
            }

            $path = $path . '.html';

            if (strpos($path, '_') === false) {
                $path = '_' . $path;
            }

            $paths[$partial] = $root . $path;
        }

        $file = $root . $file . '.html';

        //render
        return cradle('global')->template($file, $data, $paths);
    });
});

$cradle->addLogger(function($message, $request, $response) {
    $logRequest = Request::i()->load();
    $logResponse = Response::i()->load();

    //record logs
    $logRequest
        ->setStage('history_remote_address', $request->getServer('REMOTE_ADDR'))
        ->setStage('user_id', $request->getSession('me', 'user_id'))
        ->setStage('history_page', $request->getServer('REQUEST_URI'))
        ->setStage('history_activity', $message)
        ->setStage('history_meta', [
            'request' => $request->get(),
            'response' => $response->get(),
        ]);

    cradle()->trigger('history-create', $logRequest, $logResponse);
});
