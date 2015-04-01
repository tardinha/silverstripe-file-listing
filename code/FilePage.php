<?php

class FilePage extends Page {

  static $db = array(
    "FilesHeading" => "Text"
  );

  static $has_one = array(
    "Folder" => "Folder"
  );

  static $defaults = array(
    "FilesHeading" => "Downloads"
  );

  static $description = "Lists files contained within a specific assets folder, for downloading/viewing.";
  static $singular_name = "File Download Page";
  static $plural_name = "File Download Pages";
  static $icon = "file-listing/images/download";

  function getCMSFields() {
    $fields = parent::getCMSFields();

    if ($this->FolderID) {
      $filescount = File::get()->filter( array("ParentID"=>$this->FolderID) )->count();
      $fields->addFieldToTab('Root.Main',
        new LiteralField("addnew","<p><a href='/admin/assets/show/".$this->FolderID."' class='ss-ui-button ss-ui-action-constructive ui-button' style='font-size:130%' data-icon=add''>Manage Files (".$filescount.")</span></a></p>"),'Title');
    }

    $fields->insertBefore(new TextField("FilesHeading","Files Heading"),'Content');

    $site = Multisites::inst()->getActiveSite();
    $multisiteFolder = $site->Folder();

    $dropdown = TreeDropdownField::create("FolderID", "Choose a page to show on the right:", "Folder")->setTreeBaseID($multisiteFolder->ID);
    //$this->FolderID ? $dropdown->setEmptyString("Clear list") : $dropdown->setEmptyString(" ");
    $fields->insertAfter($dropdown,'FilesHeading');

    return $fields;
  }


}


class FilePage_Controller extends Page_Controller {

  public function init() {
    if (Director::fileExists(project() . "/css/files.css")) {
      Requirements::css(project() . "/css/files.css");
    } else {
      Requirements::css("file-listing/css/files.css");
    }
    parent::init();
  }


  // Returns files/folders for the current folder
  function Listing($ParentID = null) {
    if(!$this->FolderID) return false;

    if(isset($_GET['fid'])) $field = filter_var($_GET['fid'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    if (isset($field)) {
      if (DataObject::get("File", "ID = ".$field)) {
        $ParentID = $field;
      }
    } else {
      $ParentID = $this->FolderID;
    }

    if ($ParentID == $this->FolderID) {
      $list = DataObject::get("File", "ParentID = ".$ParentID,"Title ASC");
    } else {
      $list = DataObject::get("File", "ParentID = ".$ParentID,"Created DESC");
    }

    if (class_exists('FileVersion')) {
      return $list->exclude("Filename:PartialMatch", "_versions");
    } else {
      return $list;
    }

  }


  // Checks if not at the root folder
  function NotRoot() {
    if(isset($_GET['fid'])) $field = filter_var($_GET['fid'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    if (isset($field)) {
      if (DataObject::get("File", "ID = ".$field)) {
        return true;
      }
    }
    return false;
  }


  // Gets current folder from $_GET['fid']
  function CurrentFolder() {
    if(isset($_GET['fid'])) $field = filter_var($_GET['fid'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

    if (isset($field)) {
      return DataObject::get_by_id("File",$field);
    }
    return false;
  }


  // Creates link to go back to parent folder
  function BackLink() {
    if ($this->CurrentFolder()) {
      if($this->CurrentFolder()->ParentID != $this->FolderID) {
        return "?fid=".$this->CurrentFolder()->ParentID;
      } else {
        return "?";
      }
    } else {
      return false;
    }
  }


}


?>
