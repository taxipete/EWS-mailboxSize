<?php



	require_once 'php-ews/ExchangeWebServices.php';
	require_once 'php-ews/NTLMSoapClient.php';
	require_once 'php-ews/NTLMSoapClient/Exchange.php';
	require_once 'mailboxClass.php';

     $searchstr = "username";

	$myEWS = new myEWS();
	$myEWS->setUser($searchstr. "@cisco.com");

	$response = $myEWS->listAllFolders();
	$allFolders =  $myEWS->pullFromlistAllfolders($response);

     print_r($allFolders);

	exit;

?>
