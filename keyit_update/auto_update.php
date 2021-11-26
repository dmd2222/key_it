<?php



//Check first try to update this day
//#####


//Options
$file_name =dirname(__FILE__) ."auto_update_timestamp.txt";

//Check File existing
if (test_file_existing($file_name)==false){
//File does not exist, creat it
write_in_file($file_name,"");
}


//Open File
$information = read_from_file($file_name);

//Get now timestamp
$date = new DateTime();
$timestamp_now = $date->getTimestamp();


// 60 Sec. ~ 60 Sec.
// 10 Sec. ~ 10 Sec.
if ($information + (10) < $timestamp_now){
    //Last updtae check is older more than x time 

    //Redirect
   // header("Location: gitupdater/gitupdater.php");
include_once(dirname(__FILE__) . "gitupdater/gitupdater.php");

    //Write new last update timestamp
write_in_file($file_name,$timestamp_now);

}else{
// Last update is NOT older than x time

    //Redirect
    //header("Location: ../");
    echo "Last update is NOT older than x time";
}




//Functions

function read_from_file($file_name){

    try {
//Open File
        $myfile = fopen($file_name, "r") or die("Unable to open file!");

        //Read File 
        $information =  fread($myfile,filesize($file_name));
        //Close file
        fclose($myfile);


        return $information;
    } catch (Exception $e) {
        throw new Exception( $e->getMessage());
    }

}


function write_in_file($file_name,$text){

    try {

        $myfile = fopen($file_name, "w") or die("Unable to open file!");
        fwrite($myfile, $text);
        fclose($myfile);

        return true;

    } catch (Exception $e) {
        throw new Exception( $e->getMessage());
    }

}


function test_file_existing($file_name){

    if (file_exists($file_name)) {
       return true;
    } else {
       return false;
    }

}

?>
