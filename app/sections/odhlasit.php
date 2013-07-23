<?php

$_COOKIE['lopuch'] = '';
setcookie('user', '', time()-3600);
setcookie('lopuch', '', time()-3600);    
session_destroy();


$app->redirect('login?odhlasit=1');