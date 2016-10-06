<?php

spl_autoload_register(function ($class_name) {
    $base_path = 'php-ews';
    $include_file = $base_path . '/' . str_replace('_', '/', $class_name) . '.php';
	return (file_exists($include_file) ? require_once $include_file : false);

});





class myEWS {
	private $server = "your.mailserver.com";
	private $username = "username";
	private $password = "password";
	private $version = "VERSION_2013_SP1";
	private $user = 'useraccount';

	private $ews;
	private $allFolders;

	public function __construct(){
		$this->ews = new ExchangeWebServices($this->server, $this->username, $this->password);

	}


	/*
	 * Sets the user to be impersanated
	 */
	public function setUser($user){
		$this->user = $user;
		$ei = new EWSType_ExchangeImpersonationType();
		$sid = new EWSType_ConnectingSIDType();
		$sid->PrimarySmtpAddress = $this->user;
		$ei->ConnectingSID = $sid;
		$this->ews->setImpersonation($ei);	// This will need to be a new function
	}



     /**
	 * Lists folders in the mailbox and return a size for the folder
	 * @return array accossivate array of all folder details
	 */
	public function listAllFolders(){
		// start building the find folder request
		$request = new EWSType_FindFolderType();
		$request->Traversal = EWSType_FolderQueryTraversalType::DEEP;	// DEEP
		$request->FolderShape = new EWSType_FolderResponseShapeType();
		$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

		$request->FolderShape->AdditionalProperties = new EWSType_NonEmptyArrayOfPathsToElementType();

		// https://msdn.microsoft.com/en-us/library/office/cc842471.aspx
          $status = new EWSType_PathToExtendedFieldType();
          $status->PropertyTag = "0x0E08";
          $status->PropertyType = EWSType_MapiPropertyTypeType::LONG; //INTERGER
		$request->FolderShape->AdditionalProperties->ExtendedFieldURI = array($status);


		// configure the view
		$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
		$request->IndexedPageFolderView->BasePoint = 'Beginning';
		$request->IndexedPageFolderView->Offset = 0;

		// set the starting folder as the inbox
		$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
		$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
		$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_ROOT; // INBOX
		//print_r($request);
		// make the actual call
		$response = $this->ews->FindFolder($request);
		return $response;
	}





	/*
	 * Functions to processing the results
	 *
	 */

	/**
	 * Converts the results from listAllFolders into a list of all folders
	 * @param  obj $response the output from allFolders function
	 * @return array accossivate array of all folder details
	 */
	public function pullFromlistAllfolders($response){
		$folders = $response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->Folder;
		//print_r($folders);
		$totalSize = 0;
		$totalitemCount = 0;
		$allFolders = array();
		foreach($folders as $folder){
			//print_r($folder);
			$totalSize = $totalSize + $folder->ExtendedProperty->Value;
			$totalitemCount = $totalitemCount + $folder->TotalCount;
			array_push($allFolders, array(
				'DisplayName' => $folder->DisplayName,
				'TotalCount' => $folder->TotalCount,
				'size' => $folder->ExtendedProperty->Value,
				'UnreadCount'=> $folder->UnreadCount,
				'friendlySize' => $this->formatBytes($folder->ExtendedProperty->Value)
			));
		}

		// Sorts the allfolders by size
		$sort = array();
		foreach ($allFolders as $key => $row)
		{
			$sort[$key] = $row['size'];
		}
		array_multisort($sort, SORT_DESC, $allFolders);


		//print_r($allFolders);
		return array(
			'folders' => $allFolders,
			'totalSizefriendly' => $this->formatBytes($totalSize),
			'totalSize' => $totalSize / 1024,
			'totalitemCount' => $totalitemCount );
	}




	/**
	 * Standard function for converting bytes
	 * @param  [[Type]] $size            [[Description]]
	 * @param  [[Type]] [$precision = 2] [[Description]]
	 * @return [[Type]] [[Description]]
	 */
	public function formatBytes($size, $precision = 2)
	{
		if($size == 0){
			return 0;
		};
		$base = log($size, 1024);
		$suffixes = array('', 'K', 'M', 'G', 'T');

		return round(pow(1024, $base - floor($base)), $precision) .' '. $suffixes[floor($base)];
	}





}


?>
