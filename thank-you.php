<!-- 
    @author Tosin Komolafe
    @copyright CrotSoftware 2012
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
      <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
      <title>Thank you!</title>
      <link rel="STYLESHEET" type="text/css" href="style/fg_membersite.css">
</head>
<body>
<div id='fg_membersite_content'>
<h2>Thanks for registering!</h2>
Your registration is now complete.
<p>Free Account ID: <b><?php echo $_GET['uid']; ?></b></p>


<form method="post">
    <input type="button" value="Close Window" onclick="window.close()"/>
</form>
</div>
</body>
</html>