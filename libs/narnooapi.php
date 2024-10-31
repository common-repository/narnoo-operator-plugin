<?php 

include "narnoo-php-sdk/vendor/autoload.php";


use Narnoo\Connect\Connect;
use Narnoo\Business\Business;
use Narnoo\Product\Product;
use Narnoo\Booking\Booking;
use Narnoo\Media\Media;

class Narnoosdk
{

	protected $token;

	public function __construct($token){
		$this->token = $token;
	}

	/************************ connected Operator ********************/

	// used in narnoo opertor connect plugin
	public function following($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->getFollowing($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function find($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}
		
		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->findBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function search($search){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->searchBusinesses($search);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function followBusiness($connect){

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->followBusinesses($connect);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function followOperator($connect){

		$value = array("type"=>"operator","id" =>$connect );

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->followBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function removeOperator($connect){

		$value = array("type"=>"operator","id" =>$connect );

		$connect = new connect();
		$connect->setToken($this->token);
		$list 	 = $connect->removeBusinesses($value);
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	/************************ Products ********************/

	// used in narnoo opertor connect plugin
	public function getProducts( $operator = NULL ){
    	$product = new product();
		$product->setToken($this->token);
		$details 	 = $product->getProducts($operator);
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function getBookableProducts( $operator = NULL ) {
		$booking = new booking();
		$booking->setToken($this->token);
		$details 	 = $booking->getBookableProducts($operator);
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	// used in narnoo opertor connect plugin
	public function getProductDetails($id, $operator = NULL){
		
		$product = new product();
		$product->setToken($this->token);
		$details 	 = $product->getProductDetails( $id, $operator );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	
	}

	/************************ Business ********************/

	// used in narnoo opertor connect plugin
	public function getBusinessListing( $id ){
		
		$listing = new business();
		$listing->setToken($this->token);
		$list 	 = $listing->getListing( $id );
		if(!empty($list)){
			return $list;
		}else{
			return NULL;
		}
	}

	public function accountDetails() {
		$business = new business();
		$business->setToken($this->token);
		$details 	 = $business->getProfileDetail( );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	/************************ media ********************/

	public function getImages($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getImages( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}	
	}

	public function getVideos($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getVideos( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}
	
	public function getBrochures($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getPrints( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function getBrochureDetails($id){

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getPrintDetails( $id );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function getAlbums($page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getAlbums( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function getAlbumImages( $id, $page=1){

		if(!empty($page)){
			$value = array(
				'page' => $page
			);
		}

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->getAlbumImages( $id, $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function download_brochure( $id ){

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->downloadBrochure( $id );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function download_image( $id ){

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->downloadImage( $id );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function album_create( $name ) {

		$value = array( "name" => $name );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->creatAlbum( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}
	
	public function album_add_image( $albumid, $imageIds ) {

		$value = array( "albumId" => $albumid, "image" => array( array( "id" =>  $imageIds ) ) );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->addImageToAlbum( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function album_remove_image( $albumid, $imageIds ) {
		
		$value = array( "albumId" => $albumid, "image" => array( array( "id" =>  $imageIds ) ) );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->removeImageToAlbum( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function deleteImage( $imageIds ) {

		$value = array( $imageIds );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->deleteImage( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function deleteBrochure( $brochureIds ) {

		$value = array( $brochureIds );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->deleteBrochure( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

	public function deleteVideo( $videoIds ) {

		$value = array( $videoIds );

		$media = new media();
		$media->setToken( $this->token );
		$details = $media->deleteVideo( $value );
		if(!empty($details)){
			return $details;
		}else{
			return NULL;
		}
	}

}
