<?PHP
include("config.php");
// include("include.inc");
global $host,$username,$password,$community;
$db = mysql_connect("$host","$username","$password");
mysql_select_db("$database");

if(!$db){
        die("Kan geen database verbinding maken... sorry!");
}
function shownumports(){
	$query = "SELECT COUNT(*) FROM sflowtool"; 
	$numports = mysql_result(mysql_query($query),0);
	echo $numports;
        }

function shownumswitch(){
	$query = "SELECT COUNT(DISTINCT hostname) FROM sflowtool"; 
	$numswitch = mysql_result(mysql_query($query),0);
	echo $numswitch;
        }

function showSwitch(){
        $result = mysql_query("SELECT distinct hostname FROM sflowtool WHERE hostname not like 'sw-%' ORDER BY hostname ASC");
        $count = 0;
        while ($row = mysql_fetch_array($result)){
                $count++;
        $hostname = $row["hostname"];
        print "<tr class=switch><td width=145><a target=_parent href=\"?hostname=$hostname\">$hostname</a></td></tr>";
        }
}

function showSwitchRR(){
        $result = mysql_query("SELECT distinct hostname FROM sflowtool WHERE hostname like 'sw-%' ORDER BY hostname ASC");
        $count = 0;
        while ($row = mysql_fetch_array($result)){
                $count++;
        $hostname = $row["hostname"];
        print "<tr class=switch><td width=145><a target=_parent href=\"?hostname=$hostname\">$hostname</a></td></tr>";
        }
}

function showSwitchFW(){
        $result = mysql_query("SELECT distinct hostname FROM sflowtool ORDER BY hostname ASC");
        $count = 0;
        while ($row = mysql_fetch_array($result)){
                $count++;
        $hostname = $row["hostname"];
	//get system name
	$sysname[0] = snmpget($hostname, $community, "sysName.0");
	$sysname[1] = eregi_replace("STRING:","",$sysname[0]);
	echo '<tr><td><b>System Name:</b> '.$sysname[1].'</td>';

	// Get the ip address
	$agent[0] = snmpget($hostname, $community, "SNMPv2-SMI::mib-2.14.1.1.0");
	$agent = eregi_replace("IpAddress: ","",$agent[0]);
	if($agent==(""))
	{ $agent = gethostbyname($hostname); }
	echo '<td>System IP address: '.$agent.'</td>';;
	
	//system description
	$sysdesc[0] = snmpget($hostname, $community, "sysDescr.0");
	$sysdesc[1] = eregi_replace("STRING:","",$sysdesc[0]);
	echo '<td>System Description: '.$sysdesc[1].'</td></tr>';
        }
}

function showSwitchInfo($hostname,$community){
//get system name
$sysname[0] = snmpget($hostname, $community, "sysName.0");
$sysname[1] = eregi_replace("STRING:","",$sysname[0]);
echo '<h1><b>System Name:</b> '.$sysname[1].'</h1>';

// Get the ip address
//$agent[0] = snmpwalk($hostname, $community, "IP-MIB::ipAdEntAddr");
//$agent = eregi_replace("IpAddress: ","",$agent[0]);
if(empty($_GET["agent"]))
$agent = gethostbyname($hostname); 
echo '<b>System IP address:</b> '.$agent.'<br>';;

//system description
$sysdesc[0] = snmpget($hostname, $community, "sysDescr.0");
$sysdesc[1] = eregi_replace("STRING:","",$sysdesc[0]);
echo '<b>System Description:</b> '.$sysdesc[1].'<br>';

//system contact
$syscont[0] = snmpget($hostname, $community, "sysContact.0");
$syscont[1] = eregi_replace("STRING:","",$syscont[0]);
echo '<b>System Contact:</b> '.$syscont[1].'<br>';

//system location
$sysloc[0] = snmpget($hostname, $community, "sysLocation.0");
$sysloc[1] = eregi_replace("STRING:","",$sysloc[0]);
echo '<b>System Location:</b> '.$sysloc[1].'<br>';
echo "<br><br>";

// Get interface index
//$ifIndex = snmpwalk("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.1");
$ifIndex = snmpwalk("$hostname","$community","1.3.6.1.2.1.2.2.1.1");

print "<table border=1 bgcolor=#ffffff width=100%>";
print "<tr>
        <td>Port</td>
        <td>Interface</td>
        <td>Description</td>
        <td>Speed</td>
        <td>Admin state</td>
        <td>Oper state</td>
        <td>Last Change</td>
        <td>Avg. speed In</td>
        <td>Avg. speed Out</td>
        <td>Tools</td>
        </tr>";
$maxI=999;

for ($i=0; $i<count($ifIndex); $i++) {

$indexraw=$ifIndex[$i];
$index = eregi_replace("INTEGER: ","",$indexraw);

if ($index > 999) {
        break;
    }

//$ifAlias = snmpget("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.17.$index");
$ifAlias = snmpget("$hostname","$community","IF-MIB::ifAlias.$index");
$alias1 = eregi_replace("STRING:","",$ifAlias);
$alias = eregi_replace("\"","",$alias1);

$ifSpeed = snmpget("$hostname","$community","interfaces.ifTable.ifEntry.ifSpeed.$index");
$speedlarge = eregi_replace("Gauge32: ","",$ifSpeed);
if($speedlarge==(4294967295)) {
$speed = 10000; }
else {
$speed = $speedlarge /= 1000000;
}

//$ifDescr = snmpget("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.18.$index");
$ifDescr = snmpget("$hostname","$community","1.3.6.1.2.1.2.2.1.2.$index");
$descr1 = eregi_replace("STRING:","",$ifDescr);
$descr = eregi_replace("\"","",$descr1);

//$ifAdminStatus = snmpget("$hostname","$community","SNMPv2-SMI::enterprises.1991.1.1.3.3.5.1.10.$index");
$ifAdminStatus = snmpget("$hostname","$community","1.3.6.1.2.1.2.2.1.7.$index");
$admstat = eregi_replace("INTEGER:","",$ifAdminStatus);
//$admstat = eregi_replace("\([1,2]\)","",$admstat1);

//$ifOperStatus = snmpget("$hostname","$community",".1.3.6.1.4.1.1991.1.1.3.3.5.1.11.$index");
$ifOperStatus = snmpget("$hostname","$community","1.3.6.1.2.1.2.2.1.8.$index");
$operstat = eregi_replace("INTEGER:","",$ifOperStatus);
//$operstat = eregi_replace("\([1,2]\)","",$operstat1);

$ifLastChange = snmpget("$hostname","$community","interfaces.ifTable.ifEntry.ifLastChange.$index");
$lastchange=$ifLastChange;

//if ($i & 1 == 0)
if ($i % 2 == 0)
  {
        print "<tr class=even>";
  }
  else
  {
        print "<tr class=odd>";
  }
        print "<td>$index</td>";
        print "<td>$descr</td>";
        print "<td>$alias</td>";
        print "<td>$speed Mbit/s</td>";
        if (preg_match("/1/", $admstat)) {
        print "<td bgcolor=green><font color=black><b>up</b</td>"; }
        else if (preg_match("/2/", $admstat)) {
        print "<td bgcolor=red><font color=black><b>down</b></td>";
        } else {
        print "<td bgcolor=lightblue>"; }

        if (preg_match("/1/", $operstat)) {
        print "<td bgcolor=green><font color=black><b>up</b></td>"; }
        else if(preg_match("/2/", $operstat)) {
        print "<td bgcolor=red><font color=black><b>down</b></td>";
        } else {
        print "<td bgcolor=lightblue>"; }
        print "<td>$lastchange</td>";
	CalcIFUsage($agent,$index,$speed);
        print "<td><a href=\"?hostname=$hostname&agent=$agent&if=$index\">sflow</a> <a href=\"?hostname=$hostname&agent=$agent&if=$index&discards=1\">discards</a> </td>";
        print "</tr>";
}
print "</table>";

}

function CalcIFUsage($agent,$index,$speed) {
$BW = exec("./bandbreedte_web.sh $agent $index $speed");
echo $BW;
}

function showSwitchSER($community){
        $result = mysql_query("SELECT distinct hostname FROM sflowtool ORDER BY hostname ASC");
        $count = 0;
        while ($row = mysql_fetch_array($result)){
                $count++;
        $hostname = $row["hostname"];
        //get system name
        $sysname[0] = snmpget($hostname, $community, "sysName.0");
        $sysname[1] = eregi_replace("STRING:","",$sysname[0]);
        echo '<tr><td><b>System Name:</b> '.$sysname[1].'</td>';

        // Get the ip address
        $agent[0] = snmpget($hostname, $community, "SNMPv2-SMI::mib-2.14.1.1.0");
        $agent = eregi_replace("IpAddress: ","",$agent[0]);
        if($agent==(""))
        { $agent = gethostbyname($hostname); }
        echo '<td>System IP address: '.$agent.'</td>';;

        //system description
        $sysdesc[0] = snmpget($hostname, $community, "SNMPv2-SMI::enterprises.1991.1.1.1.1.2.0");
        $sysdesc[1] = eregi_replace("STRING:","",$sysdesc[0]);
        echo '<td>System Serial: '.$sysdesc[1].'</td></tr>';
        }
}

function showSwitchport($hostname){
        $result = mysql_query("SELECT slotport,iflabel,ip,portindex FROM sflowtool where hostname='$hostname' ORDER BY slotport ASC");
        $count = 0;
        while ($row = mysql_fetch_array($result)){
                $count++;
        $slotport = $row["slotport"];
        $iflabel = $row["iflabel"];
        $ip = $row["ip"];
        $portindex = $row["portindex"];
        print "<tr><td width=100%><a target=_parent href=\"?agent=$ip&if=$portindex&iflabel=$iflabel&hostname=$hostname&slotport=$slotport\">$slotport - $iflabel</a></td></tr>";
        }
}
function showGraph($agent,$if,$begin){
create_graph("sflowimg/$agent-$if-traffic-minute.gif", "-300", "Minute traffic", "$agent", "$if", "$begin" );
create_graph("sflowimg/$agent-$if-traffic-hour.gif", "-3600", "Hourly traffic", "$agent", "$if", "$begin" );
create_graph("sflowimg/$agent-$if-traffic-4hour.gif", "-14400", "4 Hourly traffic", "$agent", "$if", "$begin" );
create_graph("sflowimg/$agent-$if-traffic-day.gif", "-86400", "Daily traffic", "$agent", "$if", "$begin" );
create_graph("sflowimg/$agent-$if-traffic-week.gif", "-604800", "Weekly traffic", "$agent", "$if", "$begin");
create_graphavg("sflowimg/$agent-$if-traffic-month2.gif", "-2592000", "Monthly traffic", "$agent", "$if");
create_graphavg("sflowimg/$agent-$if-traffic-year2.gif", "-31536000", "Yearly traffic", "$agent", "$if");

echo "<table border=0 width=97%>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-minute.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-hour.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-4hour.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-day.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-week.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-month2.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='/sflow/sflowimg/$agent-$if-traffic-year2.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "</table>";
}


function create_graph($output, $start, $title, $agent, $if, $begin) {
$end=$begin;
$start2=$end+$start;
  $options = array(
    "--start=$start2",
    "--end=$end",
    "--title=$title",
    "--vertical-label=Traffic",
    "DEF:bytesInmax=ifdata/$agent-$if.rrd:bytesIn:MAX",
    "DEF:bytesOutmax=ifdata/$agent-$if.rrd:bytesOut:MAX",
    "CDEF:bpsOutmax=bytesOutmax,8,*",
    "CDEF:bpsInmax=0,bytesInmax,8,*,-",
    "AREA:bpsOutmax#dedd3a:maxout",
    "GPRINT:bpsOutmax:MAX:%1.3lf %sbps\\n",
    "AREA:bpsInmax#1e3c61:maxin",
    "GPRINT:bpsInmax:MIN:%1.3lf %sbps\\n",
    "DEF:bytesIn=ifdata/$agent-$if.rrd:bytesIn:AVERAGE",
    "DEF:bytesOut=ifdata/$agent-$if.rrd:bytesOut:AVERAGE",
    "CDEF:bpsOut=bytesOut,8,*",
    "CDEF:bpsIn=0,bytesIn,8,*,-",
    "LINE:bpsOut#FF22e9:avgout",
    "GPRINT:bpsOut:AVERAGE:%1.3lf %sbps\\n",
    "LINE:bpsIn#FFb674:avgin",
    "GPRINT:bpsIn:AVERAGE:%1.3lf %sbps\\n"
  );

  $ret = rrd_graph($output, $options);
//, count($options));
  if (! $ret) {
    echo "<b>Graph error: </b>".rrd_error()."\n";
  }
}

function create_graphavg($output, $start, $title, $agent, $if) {
  $options = array(
    "--start=$start",
    "--title=$title",
    "--vertical-label=Average traffic",
    "DEF:bytesIn=ifdata/$agent-$if.rrd:bytesIn:AVERAGE",
    "DEF:bytesOut=ifdata/$agent-$if.rrd:bytesOut:AVERAGE",
    "CDEF:bpsOut=bytesOut,8,*",
    "CDEF:bpsIn=0,bytesIn,8,*,-",
    "AREA:bpsOut#dedd3a:avgout",
    "AREA:bpsIn#8a8071:avgin"
  );

  $ret = rrd_graph($output, $options);
//, count($options));
  if (! $ret) {
    echo "<b>Graph error: </b>".rrd_error()."\n";
  }
}

function showdiscGraph($agent,$if,$iflabel,$hostname){
create_discgraph("sflowimg/$agent-$if-discards-traffic-minute.gif", "-300", "Minute discards", "$agent", "$if" );
create_discgraph("sflowimg/$agent-$if-discards-traffic-hour.gif", "-1h", "Hourly discards", "$agent", "$if" );
create_discgraph("sflowimg/$agent-$if-discards-traffic-day.gif", "-10h", "5 hour discards", "$agent", "$if" );

echo "<table border=0 width=97%>";
echo "<tr><td align=center>";
echo "<img src='sflowimg/$agent-$if-discards-traffic-minute.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='sflowimg/$agent-$if-discards-traffic-hour.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "<tr><td align=center>";
echo "<img src='sflowimg/$agent-$if-discards-traffic-day.gif' alt='Generated RRD image' align=center>";
echo "</tr></td>";
echo "</table>";
}


function create_discgraph($output, $start, $title, $agent, $if) {
  $options = array(
    "--start=$start",
    "--title=$title",
    "--vertical-label=Discards & Errors",
    "DEF:DicardsIn=ifdata/discards/$agent-$if-discards.rrd:DicardsIn:AVERAGE",
    "DEF:ErrorsIn=ifdata/discards/$agent-$if-discards.rrd:ErrorsIn:AVERAGE",
    "DEF:DicardsOut=ifdata/discards/$agent-$if-discards.rrd:DicardsOut:AVERAGE",
    "DEF:ErrorsOut=ifdata/discards/$agent-$if-discards.rrd:ErrorsOut:AVERAGE",
    "CDEF:disOut=DicardsOut,8,*",
    "CDEF:errOut=ErrorsOut,8,*",
    "CDEF:disIn=DicardsIn,8,*",
    "CDEF:errIn=ErrorsIn,8,*",
    "LINE:disOut#FF22e9:disout",
    "LINE:disIn#FFb674:disin",
    "LINE:errOut#dedd3a:errout",
    "LINE:errIn#8a8071:errin"
  );

  $ret = rrd_graph($output, $options);
  if (! $ret) {
    echo "<b>Graph error: </b>".rrd_error()."\n";
  }
}

?>
