<?php

class Operator extends WebClient {

    public $operator_url        = 'https://test-connect.narnoo.com/operator/';
    public $bckup_operator_url  = 'https://connect.narnoo.com/operator_dev/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = $authenticate;
    }

    /* public function accountDetails() {

        $method = 'account';

        $this->setUrl($this->operator_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getImages($page=NULL) {

        $method = 'images';

        if(!empty($page)){
          $this->setUrl($this->operator_url . $method.'/?page='.$page);
        }else{
          $this->setUrl($this->operator_url . $method);
        }

        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 



    public function getVideos($page=NULL) {

        $method = 'videos';

        if(!empty($page)){
          $this->setUrl($this->operator_url . $method.'/?page='.$page);
        }else{
          $this->setUrl($this->operator_url . $method);
        }
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 

    public function getBrochures($page=NULL) {

        $method = 'brochures';

        if(!empty($page)){
          $this->setUrl($this->operator_url . $method.'/?page='.$page);
        }else{
          $this->setUrl($this->operator_url . $method);
        }
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 

    public function getAlbums($cover=NULL,$page=NULL) {

        $method = 'albums';
        if(!empty($page)){
            $params['page'] = $page;
        }
        if(!empty($cover)){
            $params['cover'] = TRUE;
        }
        if(!empty($params)){
            $paramLink = http_build_query($params);
            $this->setUrl($this->operator_url . $method.'/?'.$paramLink);
        }else{
            $this->setUrl($this->operator_url . $method.'/');
        }
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }  */

    public function getDistributor($page=NULL) {

        $method = 'distributors';

        if(!empty($page)){
          $this->setUrl($this->operator_url . $method.'/?page='.$page);
        }else{
          $this->setUrl($this->operator_url . $method);
        }
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /* public function getAlbumImages($id) {

        $method = 'album_images';
        $method = $method.'/'.urlencode( $id );

        $this->setUrl( $this->bckup_operator_url . $method );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 

    public function download_brochure($id) {

        $method = 'download_brochure';
        $method = $method.'/'.$id;

        $this->setUrl($this->operator_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 


    public function download_image($id) {

        $method = 'download_image';
        $method = $method.'/'.$id;

        $this->setUrl($this->operator_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 


    public function album_create($name) {

        $method = 'album_create';


        $this->setUrl($this->operator_url . $method);
        $this->setPost( "name=".$name);
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 


    public function album_add_image($albumid,$imageId) {

        $method = 'album_add_image';


        $this->setUrl($this->operator_url . $method);
        $this->setPost( "album_id=".$albumid."&image_id=".$imageId);
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } 


    public function album_remove_image($albumid,$imageId) {

        $method = 'album_remove_image_post';


        $this->setUrl($this->operator_url . $method);
        $this->setPost( "album_id=".$albumid."&image_id=".$imageId);
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    } */

    public function getProducts() {

        $method = 'products';

        $this->setUrl($this->operator_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


    public function getProductDetails( $uid ) {

        $method = 'product';

        $this->setUrl($this->operator_url . $method .'/' . $uid);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

}

?>
