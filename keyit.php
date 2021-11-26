<?php
/*
Copyright (c) CS-Digital UG (hatungsbeschränkt) https://cs-digital-ug.de/ 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/
/* TODO
-Not redirect other way.
*/

/*Version: 1.0.0.7 */



$option_newkeydays=365; // Days until you need a new key
$filename = 'key.config';
$email_ricipiants_contacts = array("");
$key_name = "k";
$key_length = 70;


//Run update
include_once("keyit_update/auto_update_check.php");

//check file exist
if (file_exists($filename)) {


    $key =  xss_clean($_GET[$key_name]);

    //open file and read it
    $myfile = fopen($filename, "r") or die("keyit.php: 1 Unable to open file!");
    $filekey =fread($myfile,filesize($filename));


    if($key == $filekey)
    {
    //Right key

    
    //Check if  we need a new key (new key every 120 days)
    
    
    
        //check if it is more than x days
    $lastmodified= filemtime($filename);
    
    
        //whats now daytime
        $datetime = new DateTime();
        $datetime = $datetime->getTimestamp();
    
    
    /*** if file is 24 hours (86400 seconds) old then delete it ***/
    //8035200 sek ~3 monate
    //5356800 sek ~ 2 Monate
    //3456000 sek ~ 40 tage
    //2678400 sek ~ 1 Monat
    //1209600 sek ~ 14 Tage
    //864000 sek ~ 10 tage
    
    
    if( $lastmodified + ($option_newkeydays*(24*60*60)) <  $datetime){
    //Its longer than x days, create new key
    
   
    //generate new key
    $newkey = createnewkeyfile($filename, $key_length );

    //Send mail to recipients
$mail_subject ="Key changing: " . date("Y.m.d H:i:s");;
$mail_message="The key of the file " . getCurrentUrl() . " has changed to: " . $newkey;
sendmailtorecipients($email_ricipiants_contacts,$mail_subject,$mail_message,false);
    


    }else{
//Key is not old enought to get changed
    }
    
    
    
    
    }else{
        //Wrong key
        header('HTTP/1.0 403 Forbidden');
        echo 'keyit.php: You are forbidden!';
        exit;
        die;
    }





}else{
    //File does not exist
    //Create it with key
    $newkey = createnewkeyfile($filename,$key_length );

    //Send mail to recipients
$mail_subject ="Key created: " . date("Y.m.d H:i:s");;
$mail_message="The key of the file " . getCurrentUrl() . " has created: " . $newkey;
sendmailtorecipients($email_ricipiants_contacts,$mail_subject,$mail_message,false);

}






//Secure file agains reading
securekeyfileagainstreading($filename);





//Funktionen / Functions

//###########################################################


function xss_clean($data)
{
// Fix &entity\n;
$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

// Remove any attribute starting with "on" or xmlns
$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

// Remove javascript: and vbscript: protocols
$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

// Remove namespaced elements (we do not need them)
$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

do
{
    // Remove really unwanted tags
    $old_data = $data;
    $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
}
while ($old_data !== $data);

// we are done...
return $data;
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function writeinfile($filename,$text){
    $myfile = fopen($filename, "w") or die("keyit.php: 2 Unable to open file!");
    fwrite($myfile, $text);
    fclose($myfile);
}



function createnewkeyfile($filename,  $key_length ){

//create new key
$newkey = generateRandomString($key_length);

    //create new file and safe it
    writeinfile($filename,$newkey);
    //secure key file
    securekeyfileagainstreading($filename);


return $newkey;

}

function securekeyfileagainstreading($filename){
    chmod($filename, 0600);
}


function sendmailtorecipients($contacts_array,$subject,$message,$output=false){
// $contacts array
//   $contacts = array("youremailaddress@yourdomain.com","youremailaddress@yourdomain.com");
//....as many email address as you need
 
        foreach($contacts_array as $contact) {
        
        $to      =  $contact;
        mail($to, $subject, $message);

        //Outpu of sending message
        if($output == true){
            echo "keyit.php: Send mail to " . $to . "with the subject " . $subject . " and the text " . $message . "... <br>";
        }
        
        }


}


    function getCurrentUrl() {
        return ((empty($_SERVER['HTTPS'])) ? 'http' : 'https') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }




?>
