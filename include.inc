<?PHP
/**
 *  Version 1.0 ing. D. van Dijk: eerste release
 * 	
 * 
 *	History																				
 *																					
 *	Version	Date		Name				Actions											
 *	----------------------------------------------------------							
 *	v1.00	07-01-2011	ing. D. van Dijk	Initial version									
 *	
 *																						
 */

if(defined('__INCLUDE_FUNCTIONS'))
{
	return;
}
/**
 * Define to be sure that the file is only one time used. 
 *
 */
define('__INCLUDE_FUNCTIONS',true);

/**
 * Special global variable declaration for parsetime
 * @global float $GLOBALS['PARSESTART_TIME']
 * @name $PARSESTART_TIME
 */
$GLOBALS['PARSESTART_TIME'] = microtime_float ();


/**#@+
* include files needed to run
* include ( "class.mysql.inc");
* include ( "class.html.inc");
* include ( "class.html.form.inc");
* #@-*/

/**
 * Get the $_POST and $_GET parameters in a safe way 
 * <code>
 * 	safe_extract($_POST, array("param1", "param2"));
 * </code>
 * @param array $keys	array with al vars you want to make global
 * @param boolean $doSetEmpty default 1, set it to 0 if you want no Empty vars 
 * @return boolean
 */
function safe_extract( $keys, $doSetEmpty=1)
{
	if( !is_array($keys))
	{
		echo "<small><b>error in safe_extract: works with an array in include.inc</b></small>";

	}

	foreach($keys as $key)
	{
		//echo "<BR>*[$key]* ";
		$value = false;
		if( array_key_exists($key,$_POST))
		{
			//echo "POST";
			$value = $_POST[$key];
		}
		else if( array_key_exists($key,$_GET))
		{
			//echo "GET ";
			$value = $_GET[$key];
		}
		else
		{
			//echo "NOT ";
		}
		//if( $value != "")
		//{
		//	echo "**". $value. "]]";
		if( $value !==false)
		{
			$GLOBALS["{$key}"] = $value;		// unsafe !
			$GLOBALS["{$key}_slashes"] = addslashes($value);
			$GLOBALS["{$key}_url"] = urlencode($value);
			$GLOBALS["{$key}_html"] = htmlspecialchars($value);
			$GLOBALS["{$key}_url_html"] = htmlspecialchars(urlencode($value));
		}
		else
		{
			if( $doSetEmpty ==1)
			{
				$GLOBALS["{$key}"] = "";		// unsafe !
			}
		}
		//}
	}
	return true;
}

/**
 	 * create a float from microtime
 	 *
 	 * @return float microtime
 	 */
function microtime_float ()
{
	list( $usec , $sec ) = explode ( " " , microtime ());
	return ((float) $usec + (float) $sec );
}

/**
 * Redirect to an other page
 *
 * @param string $message  message to give
 * @param string $url	 where to go
 * @param integer $time the timeout
 */
function Redirect($message="",$url="",$time=1500)
{
	if( $url=="")
	{
		$url= $_SERVER['PHP_SELF'];
	}

	if( $message !="")
	{
		echo "<center><h1>$message</h1></center>";
	}
	print("<script>");
	print("function redirect() {");
	print("window.location.replace(\"$url\");");
	print("}");
	print("setTimeout(\"redirect();\", $time);");
	print("</script>");
	exit;
}

/**
 * todaysDateDutch returns todays date in Dutch like: vrijdag 09 juni 2006
 *
 * @deprecated  ?? datetimeclass  should be used!!!
 * @todo delete from al code and use datatime class
 * @return string with date
 */

Function todaysDateDutch()
{


$datumvanvandaag = date("Y-m-d");

return $datumvanvandaag ;
}

Function todaysDate()
{

$dagvandemaand = date('j');
$maandvanhetjaar = date('n');
$jaartal = date('Y');

setlocale(LC_ALL, 'nld_nld');
$datumvanvandaag = strftime("%d %b %Y", mktime(0, 0, 0, $maandvanhetjaar, $dagvandemaand, $jaartal));
return $datumvanvandaag ;
}

/**
 * returna an array with strings with given length
 *
 * @param string $deString		
 * @param integer $maxlengte	max string length
 * @return string array
 * checks for CR's first, then chop the strings in maxlengte parts
 * Filter \ out of the string when \' is given
 * Trim's result
 */
Function knipString($deString, $maxlengte){
	$aGekniptestring[] = "";
	$aTeknippen[] = "";
	$strlengte = strlen($deString);
	$startpos = 0;
	$iCounterCRArray = 0;
	$iCounterCRTeKnippen = 0;
	$iCounterWhileLoop = 0;
	$arrayCounter = 0;

	for( $iCounterCR = 0; $iCounterCR <$strlengte+1; $iCounterCR++)
	{
		if(ord(substr($deString, $iCounterCR, 1)) == 13)
		{
			$sFillArray = substr($deString, $startpos, $iCounterCRTeKnippen);
			$startpos = $startpos + $iCounterCRTeKnippen;
			$iCounterCRTeKnippen = 0;
			$aTeknippen[$iCounterCRArray] = $sFillArray;
			$iCounterCRArray++;
		}
		else
		{
			$sFillArray = substr($deString, $startpos, $iCounterCRTeKnippen);
			$iFillArrayLength = strlen($sFillArray);
			$aTeknippen[$iCounterCRArray] = $sFillArray;
		}
		$iCounterCRTeKnippen++;
	}

	while($iCounterCRArray >= $iCounterWhileLoop)
	{
		$counter = 0;
		$deString = $aTeknippen[$iCounterWhileLoop];
		$startpos = 0;
		$usedstrlength = $maxlengte;

		while($counter != 30)
		{
			while(substr($deString, $startpos, 1) == ' ' && $startpos != $maxlengte)
			{
				$startpos++;
			}
			if(substr($deString, $startpos+$usedstrlength, 1) != '')
			{
				while(substr($deString, $startpos+$usedstrlength, 1) != ' ' && $usedstrlength != '0')
				{
					$usedstrlength--;
				}
			}
			else
			{
				$counter = 29;
			}
			$subString = substr($deString, $startpos, $usedstrlength);
			$subString = trim($subString);
			$subString = stripslashes($subString);
			$aGekniptestring[$arrayCounter] = $subString;
			$startpos = $startpos + $usedstrlength;
			$usedstrlength = $maxlengte;
			$counter++;
			$arrayCounter++;
		}
		$iCounterWhileLoop++;
	}

	return $aGekniptestring;
}


?>