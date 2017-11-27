<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>SFLOW</title>
    <link rel="stylesheet" href="style.css" type="text/css"/>
<?PHP
include("functions.php");
include("config.php");

if(isset($_GET["hostname"])){
$hostname=$_GET["hostname"];}

if(isset($_GET["agent"])){
$agent=$_GET["agent"];}

if(isset($_GET["discards"])){
$discards=$_GET["discards"];}

if(isset($_GET["media"])){
$media=$_GET["media"];}

if(isset($_GET["vlans"])){
$vlans=$_GET["vlans"];}

if(isset($_GET["config"])){
$config=$_GET["config"];}

if(isset($_GET["if"])){
$if=$_GET["if"];}

if(isset($_POST["begin"])){
$begin=$_POST["begin"];}
if(empty($begin)){
$begin="-0";}

// $community = "public";
?>

  </head>
  <body>
        <div id="header">
               <a href=/sflow/index.php> <img src="/sflow/images/deltics_logo.jpg" width=200px /></a>
		<font size=-2 color=#0079d4>Aantal switches: <?php shownumswitch(); ?> Aantal poorten: <?php shownumports(); ?> 

</font>

        </div>
        <div>
            <div id="left">
<br><br>
<table cellspacing=0 border=0>
	<?php  showSwitch(); showSwitchRR(); ?>
</table>
      <ul class='nobullet'>
      </ul>

              &nbsp;
            </div>
            <div id="content">
<?php
if(empty($hostname)) {
echo "<h1>Choose a switch from the menu on the left</h1>";
}

else if(isset($_GET["discards"])) {
$ifDescr = snmpget("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.17.$if");
$linknaam1 = eregi_replace("STRING: ","",$ifDescr);
$linknaam = eregi_replace("\"","",$linknaam1);
print "<h1>Discards</h1><br>";
print "<a href=?hostname=$hostname>$hostname</a> - $linknaam";
showdiscGraph($agent,$if,$begin);
} 

else if(isset($_GET["agent"])) {
$ifDescr = snmpget("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.17.$if");
$linknaam1 = eregi_replace("STRING: ","",$ifDescr);
$linknaam = eregi_replace("\"","",$linknaam1);
print "<a href=?hostname=$hostname>$hostname</a> - $linknaam";
showGraph($agent,$if,$begin);

} else {

showSwitchInfo($hostname,$community);

}
?>

            </div>
        </div>
  </body>
</html>

