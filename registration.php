<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US"><head>
<?php /**

 * @author Tosin Komolafe

 * @copyright CrotSoftware 2012

 */





ob_start();

require_once(dirname(dirname(__FILE__)) . '/../config.php');

if(isset($_GET['url'])){

    $_SESSION['url'] = $_GET['url'];

}



if(isset($_POST['submitted'])){

    $domain = $_POST["domain"];

    $ip = $_POST["ip"];

    $phone = $_POST["phone"];

    $company = $_POST["company"];

    $email = $_POST["email"];

    $url = $_POST["url"];

    $message = setup_crotpro_free_account($phone, $company, $email, $domain, $url);

    if(empty($message[0])){

        redirectToURL("thank-you.php?uid=$message[1]");

        exit;

    }

}

?><?php function redirectToURL($url){?>
  
  <script type="text/javascript">
        window.location = "<?php echo $url; ?>"
    </script><?php }?>
  
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>CrotPro Registration</title>

  
  
  <script type="text/javascript" src="scripts/gen_validatorv31.js" />
  
  <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css" />

</head><body>
<!-- Form Code Start -->
<div id="fg_membersite">
<form id="register" action="&lt;?php echo $_SERVER[" php_self="" ];?="">'
method='post' accept-charset='UTF-8'&gt;
  <fieldset> <legend>Register</legend> <input name="submitted" id="submitted" value="1" type="hidden" />
  <div class="short_explanation">* required fields</div>
  <div class="short_explanation">&gt;&gt; This information is required
to issue you free ID to get connected to the server.</div>
  <input name="domain" id="domain" value="&lt;?php echo $CFG-&gt;wwwroot; ?&gt;" maxlength="50" type="hidden" /> <input name="ip" id="ip" value="&lt;?php echo $_SERVER[" server_addr="" ];="" ?="" type="hidden" />'
maxlength="50" /&gt; <input name="url" id="url" value="&lt;?php echo $_SESSION[" url="" ];="" ?="" type="hidden" />'
maxlength="50" /&gt;
  <div><span class="error"><?php if(!empty($message[0])) echo $message[0]; ?></span></div>
  <div class="container"> <label for="company">School / Company name*:
  </label><br />
  <input name="company" id="company" value="&lt;?php if(!empty($company)) echo $company;?&gt;" maxlength="200" type="text" /><br />
  <span id="register_company_errorloc" class="error"> </span></div>
  <div class="container"> <label for="phone">Phone*:</label><br />
  <input name="phone" id="phone" value="&lt;?php if(!empty($phone)) echo $phone;?&gt;" maxlength="50" type="text" /><br />
  <small>*phone is optional. You can use 000 if you do not want to provide the phone</small><br />
  <span id="register_phone_errorloc" class="error"> </span></div>
  <div class="container"> <label for="email">E-mail*:</label><br />
  <input name="email" id="email" value="&lt;?php if(!empty($email)) echo $email;?&gt;" maxlength="50" type="text" /><br />
  <br />
  <br />
  <span id="register_email_errorloc" class="error"> </span></div>
  <div class="short_explanation">By clicking on Submit you agree to the
<href url="&lt;?php" echo="" $url?="">Terms and Conditions</href> of the service.<br />
  </div>
  <div class="container"> <input name="Submit" value="Submit" type="submit" /> </div>
  </fieldset>
</form>
<!-- client-side Form Validations:

Uses the excellent form validation script from JavaScript-coder.com-->
<script type="text/javascript">
// <![CDATA[
    
    var frmvalidator  = new Validator("register");
    frmvalidator.EnableOnPageErrorDisplay();
    frmvalidator.EnableMsgsTogether();
    frmvalidator.addValidation("company","req","Please provide your school or company name");

    frmvalidator.addValidation("email","req","Please provide your email address");

    frmvalidator.addValidation("email","email","Please provide a valid email address");

    frmvalidator.addValidation("phone","req","Please provide your phone number");

// ]]>
</script><?php function setup_crotpro_free_account($phone, $company, $email, $domain, $url){

        global $CFG;

        global $DB;

        $error_message = '';

        $uid = '';

        require_once($CFG->dirroot. '/plagiarism/crotpro/post_xml.php');

        $service_url = $url; // pds service link

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

        return array($error_message, $uid);

    }    

?></div>

</body></html>