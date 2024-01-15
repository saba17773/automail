<?php
$app->group('/automail/Actual', function () use ($app, $auth, $accessPage) {
    $app->get('/approvemail', 'App\Actual_Loading\ActualController:approvemail');
   
});