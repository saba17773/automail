<?php
$app->group('/automail/HistoryOpenPO', function () use ($app, $auth, $accessPage) {
	$app->get('/PO', 'App\HistoryOpen_PO\HistoryOpenPOController:sendmailPO');
});
