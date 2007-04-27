<?php
define('TYPO3_MOD_PATH', '../typo3conf/ext/graytree/mod_cmd/');

$BACK_PATH='../../../../typo3/';
$MCONF['name']='web_txgraytreeCmd';
$MCONF['access']='';

$MCONF['exclude']=TRUE;
$MCONF['shy']=TRUE;

$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = 'moduleicon.gif';
$MLANG['default']['ll_ref']='LLL:EXT:graytree/mod_cmd/locallang_mod.php';

?>