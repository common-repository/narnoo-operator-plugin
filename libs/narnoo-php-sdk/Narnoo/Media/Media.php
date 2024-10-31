<?

namespace Narnoo\Media;
/**
*
*/
class Media extends \Narnoo\Base
{
    /**************************************************
    *
    *                --- IMAGES ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getImages($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/list?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getImageDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/image/details/".$id."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /**
    *   @title: Download Image
    *   @date: 30.11.2018
    *   @param: int image ID
    *   @result: JSON
    */
    public function downloadImage($id)
    {
        try{
            $url = "/image/download/".$id;
            $response = $this->callNarnooAPI("get",$url);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /**
    *   @title: Delete Image
    *   @date: 18.12.2018
    *   @param: int image ID
    *   @result: JSON
    */
    public function deleteImage($value)
    {
        try{
            $url = "/image/delete/";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    

    /**************************************************
    *
    *                --- .IMAGES ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- BROCHURES ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getPrints($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/brochure/list?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getPrintDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/brochure/details/".$id."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

     /**
    *   @title: download brochures 
    *   @date: 30.11.2018
    *   @param: int image ID
    *   @result: JSON
    */
    public function downloadBrochure($id)
    {
      
        try{
            $url = "/brochure/download/".$id;
            $response = $this->callNarnooAPI("get",$url);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /**
    *   @title: Delete brochures
    *   @date: 18.12.2018
    *   @param: int image ID
    *   @result: JSON
    */
    public function deleteBrochure($value)
    {
        try{
            $url = "/brochure/delete/";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .BROCHURES ---
    *
    **************************************************/
    /**************************************************
    *
    *                --- Videos ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getVideos($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/video/list?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getVideoDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/video/details/".$id."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /**
    *   @title: Delete video
    *   @date: 18.12.2018
    *   @param: int image ID
    *   @result: JSON
    */
    public function deleteVideo($value)
    {
        try{
            $url = "/video/delete/";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .Videos ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- Logos ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getLogos($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/logo/list?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getLogoDetails($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/logo/details/".$id."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**************************************************
    *
    *                --- .Logos ---
    *
    **************************************************/

    /**************************************************
    *
    *                --- Albums ---
    *
    **************************************************/
    
    /**
    *   @title: Get Images
    *   @date: 25.06.2018
    *   @param: array page | total
    *   @result: JSON
    */
    public function getAlbums($value)
    {   

        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );


        try{
            $url = "/album/list?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


     /**
    *   @title: Get Image Details
    *   @date: 25.06.2018
    *   @param: int image ID
    *   @param: array page | total
    *   @result: JSON
    */
    public function getAlbumImages($id,$value)
    {
        $params = [];
        if(empty($value['page'])){
            $params['page'] = 1;
        }else{
            $params['page'] = $value['page'];
        }
        $query = http_build_query( $params );

        
        try{
            $url = "/album/images/".$id."?".$query;
            $response = $this->callNarnooAPI("get",$url,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**
    *   @title: Create Album
    *   @date: 03.12.2018
    *   @param: string album name
    *   @result: JSON
    */
    public function creatAlbum($value)
    {
        
        try{
            $url = "/album/create/";
            $response = $this->callNarnooAPI("post",$url,NULL,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    /**
    *   @title: Add Image From Album
    *   @date: 03.12.2018
    *   @param: array album id with image ids
    *   @result: JSON
    */
    public function addImageToAlbum( $value )
    {
        
        try{
            $url = "/album/add_image/";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }
    

    /**
    *   @title: Remove Image From Album
    *   @date: 03.12.2018
    *   @param: array album id with image ids
    *   @result: JSON
    */
    public function removeImageToAlbum( $value )
    {
        
        try{
            $url = "/album/remove_image/";
            $response = $this->callNarnooAPI("post",$url,NULL,$value);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }
    


    /**************************************************
    *
    *                --- .Albums ---
    *
    **************************************************/



}

?>