<?php
    require_once(dirname(dirname(__FILE__)) . '/../config.php');
    if(isset($_POST["Register"])){
        $domain = $_POST["domain"];
        $ip = $_POST["ip"];
        $phone = $_POST["phone"];
        $company = $_POST["company"];
        $email = $_POST["email"];
        if(!(empty($domain) || empty($ip) ||empty($phone) ||empty($company) ||empty($email))){
            if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
                if(true){
                    setup_crotpro_free_account($phone, $company, $email);
                }else{
                    echo "<hr><font color=\"red\"><b>Phone number is required.<br>Please enter a phone number with the format (xxx)xxx-xxxx eg +1(202)894-9404 or (202)889-9094</b></font></i><hr>";
                }
            }else{
                echo "<hr><font color=\"red\"><b>Invalid Email Address</b></font></i><hr>";
            }
        }else{
           echo "<hr><font color=\"red\"><b>Please fill all the boxes</b></font></i><hr>";
        }
    }
    echo "REGISTRATION Warning";
    echo "<br/>the following information will be sent to CrotSoftware for registration";
    echo "<li>Your domain name:<b>";
    require_once("../../config.php");
    global $CFG;
    echo $CFG->wwwroot;
    echo "</b>";
    echo "<li>Your server IP address: <b>"; echo $_SERVER['SERVER_ADDR']; 
    echo "</b><br/>";
    echo "This information is required to issue you free ID to get connected to the server. Please click on the button below to proceed<br/>";
    "OPTIONAL Information";
?>

    <form method="post" action="registration.php" name="reg" id="reg">
      <table style="text-align: left; width: 624px; height: 179px;" border="1" cellpadding="2" cellspacing="2">
           <tbody>
            <tr>
              <td style="width: 185px;">School / Company name&nbsp; </td>
              <td style="width: 289px;"><textarea cols="40" rows="1" name="company"><?php if(!empty($company)) echo $company;?></textarea></td>
           </tr>
           <tr>
               <td style="width: 185px;">Phone</td>
               <td style="width: 289px;"><textarea cols="40" rows="1" name="phone"><?php if(!empty($phone)) echo $phone;?></textarea></td>
           </tr>
           <tr>
            <td style="width: 185px;">E-mail&nbsp;</td>
            <td style="width: 289px;"><textarea cols="40" rows="1" name="email"><?php if(!empty($email)) echo $email;?></textarea></td>
           </tr>
          </tbody>
      </table>
      <input name="domain" value="<?php  echo $CFG->wwwroot;?>" type="hidden"><br>
        <input name="ip" value="<?php echo $_SERVER['SERVER_ADDR'];?>" type="hidden"><br>&nbsp;
        <button name="Register"><br>Register </button><br> <br>
    </form>
<br/>

<?php 
    function setup_crotpro_free_account($phone, $company, $email){
        global $CFG;
        global $DB;
        require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');
        $plagiarismsettings = (array)get_config('plagiarism');
        $service_url = $plagiarismsettings['crotpro_service_url']; // pds service link
        $params = array(
            "ph" => $phone,
            "com" => $company,
            "em" => $email
        );
        $port = 80;
        $url = $service_url.'/create_account.php';
        echo "Setting up CrotPro free account<br><hr>";
        $response = xml_post($params, $url, $port); // sends file to the web service
        $xml = new DOMDocument();
        if($xml->loadXML($response)){ // get back response
           $m = $xml->getElementsByTagName("message");
           $message = '';
           if($m->length <> 0){
               foreach($m as $value){
                   $message = $value->nodeValue;
               }
           }

           if($message == 'OK'){
                $t = $xml->getElementsByTagName("uid");
                $uid = '';
                if($t->length <> 0){
                    foreach($t as $value){
                        $uid = $value->nodeValue;
                    }
                }
               if($uid != ''){
                   if ($configfield = $DB->get_record('config_plugins', array('name'=>'crotpro_account_id', 'plugin'=>'plagiarism'))) {
                        $configfield->value = $uid;
                        if (! $DB->update_record('config_plugins', $configfield)) {
                            error("errorupdating");
                        }               
                   }
                }  
           }
           echo "<i>- <font color=\"green\"><b>OK</b></font></i><hr>";
           echo "Message from server: <font color=\"green\"><i>".$message."</i></font><br/><br/>";
        }
        flush();
    }
?>