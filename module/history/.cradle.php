<?php //-->
include_once __DIR__ . '/src/events.php';

use Cradle\Module\History\Service as HistoryService;
use Cradle\Module\Utility\ServiceFactory;

use Cradle\Http\Request;
use Cradle\Http\Response;

ServiceFactory::register('history', HistoryService::class);

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
