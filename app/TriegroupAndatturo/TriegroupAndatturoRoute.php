<?php

$app->group('/automail/TriegroupAndatturo', function () use ($app, $auth, $accessPage) {
	$app->get('/weekly', 'App\TriegroupAndatturo\TriegroupAndatturoController:sendmail');
	$app->get('/weekly_v2', 'App\TriegroupAndatturo\TriegroupAndatturoController:sendmail_V2');
	$app->get('/weekly_internal', 'App\TriegroupAndatturo\TriegroupAndatturoController:sendmail_internal');
	$app->get('/weekly_internal_test', 'App\TriegroupAndatturo\TriegroupAndatturoController:sendmail_internal_test');
});
