<?php
// +----------------------------------------------------------------------+
// | Bx                                                                   |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2007 Liip AG                                      |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or        |
// | modify it under the terms of the GNU General Public License (GPL)    |
// | as published by the Free Software Foundation; either version 2       |
// | of the License, or (at your option) any later version.               |
// | The GPL can be found at http://www.gnu.org/licenses/gpl.html         |
// +----------------------------------------------------------------------+
// | Author: Liip AG      <devel@liip.ch>                              |
// +----------------------------------------------------------------------+
/**
 * class bx_plugins_gallery
 * @package bx_plugins
 * @subpackage gallery
 * */
class bx_plugins_gallery extends bx_plugin {
    
    
    static private $instance = array();
    static private $idMapper = null;
    
    public function getPermissionList() {
    	return array(	"gallery-back-edit_image",
    					"gallery-back-gallery");	
    }
    
    public static function getInstance($mode) {
        if (!isset(bx_plugins_gallery::$instance[$mode])) {
            bx_plugins_gallery::$instance[$mode] = new bx_plugins_gallery($mode);
        } 
        return bx_plugins_gallery::$instance[$mode];
    }
    
    public function getIdByRequest($path, $name = NULL, $ext =NULL) {
        if ($ext == "html") {
        return "$name.gallery.$ext";
        } else {
            return "$name.$ext";
        }
    }
    
    public function isRealResource($path, $id) {
        return TRUE;
    }
    
    public function getResourceById($path,$id, $mock = false) {
        return new bx_resources_file($this->getGalleryRoot($path).$id);
    }
    public function adminResourceExists ($path, $id, $ext=null, $sample = false) {
        $mimetype = popoon_helpers_mimetypes::getFromFileLocation($this->getGalleryRoot($path.$id).".".$ext);
        if (strpos ($mimetype, "image") !== false) {
            return $this;
        }
        /*if (strpos ($mimetype, "application") !== false) {
            return $this;
        }*/
        
        return false;
        
    }

    public function resourceExistsById($path,$id) {
    }

    public function getContentById($path, $id) {
        $options = array();
        $options['mode'] = 'page';
        $dom = new domDocument();
        $drivers = array();
        
        $userdriver = $this->getParameter($path, 'driver');
        if (null !== $userdriver) {
            $driverClass = __CLASS__."_".$userdriver;
            if (class_exists($driverClass)) {
                $drivers[] = call_user_func(array($driverClass, 'getInstance'), $dom, $path, $id);
            }
        }
        
        if ($flickrParams = $this->getParameterAll($path,"flickr")) {
            if ($f = bx_plugins_gallery_flickr::getInstance($dom,$path,$id,$flickrParams)) {
              $drivers[] = $f; 
            }
        }
        if (count($drivers) == 0) {
            $drivers[] = bx_plugins_gallery_file::getInstance($dom,$path,$id);
        }
        
        
        //
        // have a look if the user like to show description and/or title 
        // in the gallery-page-mode
        //
        ($this->getParameter($path, "descriptionInOverview") == "true") ? $options['descriptionInOverview'] = true : $options['descriptionInOverview'] = false;
        
        ($this->getParameter($path, "titleInOverview") == "true") ? $options['titleInOverview'] = true : $options['titleInOverview'] = false;
        
        $dirInfo = pathinfo($id);
        if($dirInfo['dirname'] != '.') {
            $relPath = $dirInfo['dirname'].'/';
        } else {
            $relPath = '';
        }
        
        if($dirInfo['basename'] != 'index.gallery.html') {
            $options['mode'] = 'image';
        }
        
        $virtualRoot = $this->getVirtualGalleryRoot($path);
        
        
        $options['root'] = $this->getGalleryRoot($path).$relPath;
        
        $dom->appendChild($dom->createElement('gallery'));
        $options['path'] =  $this->getVirtualGalleryRoot($path).$relPath;
        $dom->documentElement->setAttribute('path', $options['path']);
        $dom->documentElement->setAttribute('mode', $options['mode']);
        $dom->documentElement->setAttribute('collUri', $path);
        $options['basename']  = $dirInfo['basename'];
        /*if($options['mode'] == 'image') {
            $dom->documentElement->setAttribute('imageHref', $dirInfo['basename']);
        }*/
        
        $options['images'] = $dom->createElement('images');
        $options['albums'] = $dom->createElement('albums');

        $options['numberOfImages'] = 0;
        $options['numberOfAlbums'] = 0;
        $options['numberOfCurrentImage'] = 0;

        // calculate images per page
        
        $options['rowsPerPage'] = $this->getParameter($path, 'rowsPerPage');
        $options['columnsPerPage'] = $this->getParameter($path, 'columnsPerPage');
        $options['imagesPerPage'] = $options['rowsPerPage'] * $options['columnsPerPage'];

        // get currentPage from request vars
        $options['currentPage'] = 1;
        if(isset($_REQUEST['p']) && ($_REQUEST['p'] >= 1)) {
            $options['currentPage'] = (int) $_REQUEST['p'];
        }
       
       foreach ($drivers as $d) {
             $d->getImagesAndAlbums($options);
        }

        
        if ($flickrParams) {
            $f = new Services_flickr($flickrParams['userid']);
            $sets = $f->getPhotoSets();
            foreach($sets as $set) {
                $node = $dom->createElement('album');
                $node->setAttribute('name', $set['title']);
                $node->setAttribute('href', $path.'flickr'.$set['id']."/");
                $options['albums']->appendChild($node);
                $options['numberOfAlbums']++;
            }
        }
        
        
        // get the current pagenumber if we are in "image-mode"
        if ($options['mode'] == 'image') {
            $pageNumber = ($options['numberOfCurrentImage'] -1 )/ $options['imagesPerPage'];
            $pageNumber = floor( $pageNumber);
            $options['currentPage'] = $pageNumber + 1;
            
        }
        // create a new pager
        $pagerID = 'gallery';
        bx_helpers_pager::initPager($pagerID);
        
        // update pager and create pager node
        bx_helpers_pager::setNumberOfEntries($pagerID, $options['numberOfImages']);
        bx_helpers_pager::setEntriesPerPage($pagerID, $options['imagesPerPage']);
        bx_helpers_pager::setCurrentPage($pagerID, $options['currentPage']);
        $pagerNode = $dom ->createElement('pager');
        $pagerNode->setAttribute('numberOfEntries', $options['numberOfImages']);
        $pagerNode->setAttribute('numberOfPages', bx_helpers_pager::getNumberOfPages($pagerID));
        $pagerNode->setAttribute('nextPage', bx_helpers_pager::getNextPage($pagerID));
        $pagerNode->setAttribute('prevPage', bx_helpers_pager::getPrevPage($pagerID));
        $pagerNode->setAttribute('currentPage', $options['currentPage']);     
        
        
        // get needed parameters from .config.xml file
        $params = $dom->importNode($this->getXMLNodeByParameters($path, array('rowsPerPage', 'columnsPerPage')), TRUE);

        
        $albumTree = $dom->importNode($this->getAlbumTree($this->getGalleryRoot($path), $relPath), TRUE);

        $dom->documentElement->appendChild($options['images']);
        $dom->documentElement->appendChild($options['albums']);
        $dom->documentElement->appendChild($params);
        $dom->documentElement->appendChild($pagerNode);
        $dom->documentElement->appendChild($albumTree);
	
	
	$element = $dom->createElement('lightBoxOn', $this->getParameter($path, "lightBoxOn"));
	$dom->documentElement->appendChild($element);
	
        return $dom;
        
    }

    protected function getGalleryRoot($uri = '') {
        if ($root = $this->getParameter($uri, 'virtualDir')) {
            return BX_OPEN_BASEDIR.$root;
        } else {
            if (substr($uri,0,1) != "/") {
                $uri = '/'.$uri;
            }
            return BX_OPEN_BASEDIR.'files/_galleries'.$uri;
        }
    }

    protected function getVirtualGalleryRoot($uri) {
        if ($root = $this->getParameter($uri, 'virtualDir')) {
            return $root;
        } else {
            return 'files/_galleries'.$uri;
        }
    }
    
    protected function getAlbumTree($root, $path) {
        $dom = new DomDocument();

        $treeNode = $dom->createElement('albumTree');
        
        $dirs = array('.');
        $dirs = array_reverse(explode('/', $path));
        
        // reversely loop through the array and cut dirs off one by one
        $p = $path;
        $i = 0;
        foreach($dirs as $dir) {
            $p = substr($p, 0, - (strlen($dir) + 1));
            $absPath =  $root.$p;
            if(file_exists($absPath) && is_readable($absPath) && is_dir($absPath)) {
                $name = isset($dirs[$i+1]) ? $dirs[$i+1] : '';
                $newParent = $dom->importNode($this->createCollNodeWithChildren($absPath, $name, $dir, $p), TRUE);
                if(isset($parent)) {
                    $newParent->firstChild->appendChild($parent);
                }
                $parent = $newParent;
            }
            $i++;
        }

        
        $treeNode->appendChild($parent);
        return $treeNode;
        
    }
    
    protected function createCollNodeWithChildren($absPath, $dir, $parentDir, $parentPath) {
        $dom = new DomDocument();
        
        // bx_log::log("getting children for $absPath aka $dir (p == $parentPath)");
        
        $collNode = $dom->createElement('collection');
        $collNode->setAttribute('selected', 'all');
        $itemsNode = $dom->createElement('items');
        $collNode->appendChild($itemsNode);
        $collNode->appendChild($dom->createElement('title', $dir));
        $collNode->appendChild($dom->createElement('uri', "$parentPath/"));
        $collNode->appendChild($dom->createElement('display-order', 1));
        $dirIter = new DirectoryIterator($absPath);
        foreach ($dirIter as $file) {
            if(!$file->isDot() && $file->isReadable() && $file->isDir() && ($file->getFileName() != $parentDir)) {
                $itemNode = $dom->createElement('collection');
                $itemNode->setAttribute('selected', 'all');
                $itemNode->appendChild($dom->createElement('title', $file->getFileName()));
                $itemNode->appendChild($dom->createElement('uri', $parentPath.'/'.$file->getFileName().'/'));
                $itemNode->appendChild($dom->createElement('display-order', 1));
                $itemsNode->appendChild($itemNode);
            }
        }

        return $collNode;
    }
    
    public function getResourceTypes() {
        
        return array("gallery","file","archive");
    }
   
   public function addResource($name, $parentUri, $options=array(), $resourceType = null, $returnAfterwards = FALSE) {
	   //d();
       // if not collection, then it's a file
       if (!isset($options['collection'])) {
           $parts = bx_collections::getCollectionUriAndFileParts($parentUri);
           $rootPath = $this->getGalleryRoot($parts['colluri']);
           $tmpname = $_FILES['bx']['tmp_name']['plugins']['admin_addresource']['file'];
           //some IEs send full path instead of just the filename
           // basename() cuts that off
           
           $filename = basename($_FILES['bx']['name']['plugins']['admin_addresource']['file']);
           // prevent illegal filenames
           $filename = bx_helpers_string::makeUri($filename,true);
           $to = $rootPath .$parts['rawname']. $filename;
           bx_helpers_file::mkPath(dirname($to));  
           $id = str_replace(BX_OPEN_BASEDIR,"/",$to);
           $mimetype = popoon_helpers_mimetypes::getFromFileLocation($filename);
           if ($resourceType != 'archive' && strpos($mimetype,'image/') !== 0) {
               if($returnAfterwards)
                   return FALSE;

               print "$filename is not an image and can't be handled by the gallery plugin";
               exit(0);
           }
           
           bx_plugins_file::addFileResource($tmpname,$to, $parentUri,$resourceType,$options,$id);  
           $id =( $parentUri. $filename);
           $r = $this->getResourceById(substr($parentUri,1), $filename);
           $r->onSave();
           
           if($returnAfterwards == TRUE) 
            return $id;
           
           if ($resourceType == "archive") {
               header("Location: ".BX_WEBROOT."admin/addresource/".$parentUri."?type=archive&updateTree=$parentUri");
           } else {
               header("Location: ".BX_WEBROOT."admin/edit/".$id."?updateTree=$parentUri");
           }
                
           exit(0);
       } else {
           
           //$name = $options['directory'];
           
           $coll = bx_helpers_string::makeUri($options['collection']);
           $name = "/$parentUri/$coll/";
           $filesPath = "files/_galleries/$name/";
           $filesPath = str_replace('//','/',$filesPath);
           bx_helpers_file::mkPath(BX_OPEN_BASEDIR."/".$filesPath);        
           
           $coll = new bx_collection($parentUri.$coll."/","output", true);
           $coll->setProperty("display-order",99);
           $config = file_get_contents(BX_LIBS_DIR."plugins/gallery/config.xml");
           $config = str_replace("{path}",$filesPath,$config);
           file_put_contents(BX_DATA_DIR.$coll->uri.BX_CONFIGXML_FILENAME,$config);
           file_put_contents(BX_DATA_DIR.$coll->uri.BX_CONFIGXML_FILENAME.'.children',$config);
           $location = sprintf("%sadmin/addresource/%s?type=file&updateTree=$parentUri", BX_WEBROOT, $name);
           
           header("Location: $location");
       }
        return false;
    }
    public function getAddResourceParams($type, $path) {
        
        if ($type != "gallery") {
            return bx_plugins_file::getAddResourceParams($type,$path, array("junkpaths" => true,"fixinvalid" => true));
        }
         $dom = new domDocument();

        $fields = $dom->createElement('fields');

/*        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'directory');
        $nameNode->setAttribute('type', 'text');
        $nameNode->setAttribute('value', '_galleries'.$path);
        $nameNode->setAttribute('size', '50');
        $nameNode->setAttribute('textBefore', '/files/');
        $nameNode->appendChild($dom->createElement("help","Where to put the pictures in the '/files/' directory. The default is just fine."));
        $fields->appendChild($nameNode);
  */
      $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'collection');
        $nameNode->setAttribute('type', 'text');
        $nameNode->setAttribute('value', 'gallery');
        $nameNode->setAttribute('size', '50');
        $nameNode->appendChild($dom->createElement("help","How the new collection should be called."));
        $fields->appendChild($nameNode);
    
    
        $nameNode = $dom->createElement('field');
        $nameNode->setAttribute('name', 'name');
        $nameNode->setAttribute('type', 'hidden');

        $fields->appendChild($nameNode);

       
        $dom->appendChild($fields);

        return $dom;
    }
    public function getChildren($coll, $id) {
        $root = $this->getGalleryRoot($coll->uri);
        $virtual = $this->getVirtualGalleryRoot($coll->uri);
        if (file_exists($root)) {
            $dir = new DirectoryIterator($root);
            $ch = array();
            foreach ($dir  as $file) {

                $name = $file->getFileName();
                if (strpos($name,".") === 0) {
                    continue;
                }
                if ($file->isDir()) {
                } else  {
                    $ch[] = new bx_resources_file($root.$name);
                }
            }
            return $ch;
        }
        return array();
    }

        
    public function collectionCopy($point,$from, $to, $move) {
        if ($point == 'before') {
            bx_helpers_file::mkpath($this->getGalleryRoot($to));
        }
    }
    
   public function collectionDelete($point,$dir) {
        if ($point == 'after') {
            @rmdir($this->getGalleryRoot($dir));
        }
    }
    
    public function getAbsoluteFileRoot($uri) {
        return $this->getGalleryRoot($uri);
    }    	
    
    public function getOverviewSections($path,$mainOverview) {
	    $perm = bx_permm::getInstance();
	    	
	    $sections = array();
	    $dom = new bx_domdocs_overview();
	    $dom->setTitle("Gallery");
	    $dom->setPath($path);
	    $dom->setIcon("gallery");

	    if($perm->isAllowed($path,array('collection-back-create'))) {
	        $dom->addLink("Upload single image",'addresource'.$path.'?type=file');
	        $dom->addLink("Upload multiple images in a zip",'addresource'.$path.'?type=archive');
		}	
		
	    return $dom;
    }
}

