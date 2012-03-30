<?php
ob_start();
require_once(dirname(dirname(__FILE__)) . '/../config.php');

if(isset($_POST['submitted'])){
    $domain = $_POST["domain"];
    $ip = $_POST["ip"];
    $phone = $_POST["phone"];
    $company = $_POST["company"];
    $email = $_POST["email"];
    $error_message = setup_crotpro_free_account($phone, $company, $email, $domain);
    if(empty($error_message)){
        redirectToURL('thank-you.html');
        exit;
    }
}
?>

<?php
function redirectToURL($url){?>
<script type="text/javascript">
    window.location = "<?=$url?>"
</script>
<?}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
    <title>CrotPro Registration</title>
    <script type='text/javascript' src='scripts/gen_validatorv31.js'></script>
    <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css" />
<body>

<!-- Form Code Start -->
<div id='fg_membersite'>
    
    <form id='register' action='<?php echo $_SERVER['PHP_SELF'];?>' method='post' accept-charset='UTF-8'>
        <fieldset>
            <legend>Register</legend>

            <input type='hidden' name='submitted' id='submitted' value='1'/>

            <div class='short_explanation'>* required fields</div>
            <div class='short_explanation'>>> This information is required to issue you free ID to get connected to the server.</div>            
            <input type='hidden' name='domain' id='domain' value='<?php echo $CFG->wwwroot; ?>' maxlength="50"/>
            <input type='hidden' name='ip' id='ip' value='<?php echo $_SERVER['SERVER_ADDR']; ?>' maxlength="50" />
            <div><span class='error'><?php if(!empty($error_message)) echo $error_message; ?></span></div>
            <div class='container'>
                <label for='company' >School / Company name*: </label><br/>
                <input type='text' name='company' id='company' value='<?php if(!empty($company)) echo $company;?>' maxlength="200" /><br/>
                <span id='register_company_errorloc' class='error'></span>
            </div>
            <div class='container'>
                <label for='phone' >Phone*:</label><br/>
                <input type='text' name='phone' id='phone' value='<?php if(!empty($phone)) echo $phone;?>' maxlength="50" /><br/>
                <span id='register_phone_errorloc' class='error'></span>
            </div>
            <div class='container'>
                <label for='email' >E-mail*:</label><br/>
                <input type='text' name='email' id='email' value='<?php if(!empty($email)) echo $email;?>' maxlength="50" /><br/>
                <span id='register_email_errorloc' class='error'></span>
            </div>

            <div class='container'>
                <input type='submit' name='Submit' value='Submit' />
            </div>

        </fieldset>
    </form>
<!-- client-side Form Validations:
Uses the excellent form validation script from JavaScript-coder.com-->

<script type='text/javascript'>
// <![CDATA[
    
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("company","req","Please provide your school or company name");

    frmvalidator.addValidation("email","req","Please provide your email address");

    frmvalidator.addValidation("email","email","Please provide a valid email address");

    frmvalidator.addValidation("phone","req","Please provide your phone number");

// ]]>
</script>

<?php 
    function setup_crotpro_free_account($phone, $company, $email, $domain){
        global $CFG;
        global $DB;
        $error_message = '';
        require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');
        $plagiarismsettings = (array)get_config('plagiarism');
        $service_url = $plagiarismsettings['crotpro_service_url']; // pds service link
        $params = array(
            "domain"=> $domain,
            "ph" => $phone,
            "com" => $company,
            "em" => $email
        );
        $port = 80;
        $url = $service_url.'/create_account.php';
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
           }else{
              $error_message =  "<font color=\"green\"><i>".$message."</i></font>"; 
           } 
        }
        flush();
        return $error_message;
    }    
?>
</body>
</html>