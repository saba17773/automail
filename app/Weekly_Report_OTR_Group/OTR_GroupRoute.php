<?php

$app->group('/automail/OTR_Group', function () use ($app, $auth, $accessPage) {
	$app->get('/weekly', 'App\Weekly_Report_OTR_Group\OTR_GroupController:sendmail');
	$app->get('/smith', 'App\Weekly_Report_OTR_Group\OTR_GroupController:sendmailBlacksmith');
	$app->get('/OTR', 'App\Weekly_Report_OTR_Group\OTR_GroupController:sendmailOTR');
});
