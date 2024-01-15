<?php

$app->group('/automail/docpleaseconf', function () use ($app, $auth, $accessPage) {
	$app->get('/sendmail', 'App\doc_please_comfirm\DoconfirmController:sendmail');
});
