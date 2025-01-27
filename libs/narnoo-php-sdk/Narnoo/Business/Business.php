<?

namespace Narnoo\Business;
/**
*
*/
class Business extends \Narnoo\Base
{
    /**************************************************
    *
    *                --- Listing ---
    *
    **************************************************/
    
     /**
    *   @title: Get Listing
    *   @date: 25.06.2018
    *   @param: int business ID [optional]
    *   @result: JSON
    */
    public function getListing($value = NULL)
    {   
        
        try{
            $url = "/business/listing";
            if( !empty($value) ){
                $url .= "/".$value;
            }
            $response = $this->callNarnooAPI("get",$url);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }


    
    /**************************************************
    *
    *                --- .Listing ---
    *
    **************************************************/



    /**************************************************
    *
    *                --- Profile ---
    *
    **************************************************/

    /**
    *   @title: Get Profile
    *   @date: 03.12.2018
    *   @result: JSON
    */
    public function getProfileDetail()
    {   
        
        try{
            $url = "/business/profile";
            $response = $this->callNarnooAPI("get",$url);
            return $response;
        } catch (Exception $e) {
            $response = array("error" => $e->getMessage());
            return $response;
        }
    }

    /**************************************************
    *
    *                --- Profile ---
    *
    **************************************************/

}

?>