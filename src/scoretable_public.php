<?php
////////////////////////////////////////////////////////////////////////////////
//BOCA Online Contest Administrator
//    Copyright (C) 2003-2013 by BOCA System (bocasystem@gmail.com)
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 3 of the License, or
//    (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//    You should have received a copy of the GNU General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////
//Last updated 02/sep/2013 by cassio@ime.usp.br
require_once("db.php");
if(isset($_GET["problems"])){
    $rest=json_encode(array_map(function($t){
        $t["balloon"]=$loc . "/balloons/" . md5($t["color"]) . '.png';;
        unset($t["descfilename"]);
        unset($t["descoid"]);
        return $t;
    },DBGetProblems($_SESSION["usertable"]["contestnumber"])),JSON_PRETTY_PRINT);
    echo $rest;
    return 200;
}
if(isset($_SESSION["locr"]))
    $locr=$_SESSION["locr"];
else
    $locr='.';
if(isset($_GET["clock"]) && $_GET["clock"]==1) {
	ob_start();
	header ("Expires: " . gmdate("D, d M Y H:i:s") . " GMT");
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header ("Cache-Control: no-cache, must-revalidate");
	header ("Pragma: no-cache");
	header ("Content-Type: text/json; charset=utf-8");
	session_start();
	ob_end_flush();

	if(!isset($contest) || !isset($localsite)) {
		$ct=DBGetActiveContest();
		$contest=$ct['contestnumber'];
		$localsite=$ct['contestlocalsite'];
	}
	if (($blocal = DBSiteInfo($contest, $localsite)) == null) {
		echo "0";
		exit;
	}
	if(isset($blocal['currenttime']))
		echo $blocal["currenttime"];
	else echo "0";
	exit;
}
$loc = $_SESSION["loc"];
if(!isset($detail)) $detail=true;
if(!isset($final)) $final=false;
$scoredelay["admin"] = 3;
$scoredelay["score"] = 30;
$scoredelay["team"] = 10;
$scoredelay["judge"] = 5;
$scoredelay["staff"] = 30;
$actualdelay = 60;

if(isset($scoredelay[$_SESSION["usertable"]["usertype"]])) $actualdelay = $scoredelay[$_SESSION["usertable"]["usertype"]];
$ds = DIRECTORY_SEPARATOR;
if($ds=="") $ds = "/";

$scoretmp = $_SESSION["locr"] . $ds . "private" . $ds . "scoretmp" . $ds . $_SESSION["usertable"]["usertype"] . '-' . $_SESSION["usertable"]["username"] . ".php";
$redo = TRUE;
if(file_exists($scoretmp)) {
	if(($strtmp = file_get_contents($scoretmp,FALSE,NULL,-1,100000)) !== FALSE) {
		list($d) = sscanf($strtmp,"%*s %d");
		if($d > time() - $actualdelay) {
			$redo = FALSE;
		}
	}
}

if($_SESSION["usertable"]["usertype"]=='score' || $_SESSION["usertable"]["usertype"]=='admin' || (isset($_GET["remote"]) && is_numeric($_GET["remote"]))) {
  $privatedir = $_SESSION['locr'] . $ds . "private";
  $remotedir = $_SESSION['locr'] . $ds . "private" . $ds . "remotescores";
  $destination = $remotedir . $ds ."scores.zip";
  if(is_writable($remotedir)) {
	if($redo || !is_readable($destination)) {
	  if(($fp = @fopen($destination . ".lck",'x')) !== false) {

		if (($s = DBSiteInfo($_SESSION["usertable"]["contestnumber"],$_SESSION["usertable"]["usersitenumber"])) == null)
			ForceLoad("index.php");

		$level=$s["sitescorelevel"];
		$data0 = array();
		if($level>0) {
			list($score,$data0) = DBScoreSite($_SESSION["usertable"]["contestnumber"],
											  $_SESSION["usertable"]["usersitenumber"], 0, -1);
		}
		$ct=DBGetActiveContest();
		$localsite=$ct['contestlocalsite'];
		$fname = $privatedir . $ds . "score_localsite_" . $localsite . "_x"; // . md5($_SERVER['HTTP_HOST']);
		@file_put_contents($fname . ".tmp",base64_encode(serialize($data0)));
		@rename($fname . ".tmp",$fname . ".dat");

		$data0 = array();
		if($level>0) {
			list($score,$data0) = DBScoreSite($_SESSION["usertable"]["contestnumber"],
											  $_SESSION["usertable"]["usersitenumber"], 1, -1);
		}
		$ct=DBGetActiveContest();
		$localsite=$ct['contestlocalsite'];
		$fname = $remotedir . $ds . "score_site" . $localsite . "_" . $localsite . "_x"; // . md5($_SERVER['HTTP_HOST']);
		@file_put_contents($fname . ".tmp",base64_encode(serialize($data0)));
		@rename($fname . ".tmp",$fname . ".dat");
		scoretransfer($fname . ".dat", $localsite);

		if(@create_zip($remotedir,glob($remotedir . '/*.dat'),$fname . ".tmp") != 1) {
			LOGError("Cannot create score zip file");
			if(@create_zip($remotedir,array(),$fname . ".tmp") == 1)
				@rename($fname . ".tmp",$destination);
		} else {
			@rename($fname . ".tmp",$destination);
		}
		@fclose($fp);
		@unlink($destination . ".lck");
	  } else {
			if(file_exists($destination . ".lck",'x') && filemtime($destination . ".lck",'x') < time() - 180)
				@unlink($destination . ".lck");
	  }
	}
  }
}

if(!$redo) {
	$conf=globalconf();
}
if($redo) {
	$strtmp = "<script language=\"JavaScript\" src=\"" . $loc . "/hide.js\"></script>\n";
	$pr = DBGetProblems($_SESSION["usertable"]["contestnumber"]);

	$ct=DBGetActiveContest();
	$contest=$ct['contestnumber'];
	$duration=$ct['contestduration'];

	if(!isset($hor)) $hor = -1;
	if($hor>$duration) $hor=$duration;

	$level=$s["sitescorelevel"];
	if($level<=0) $level=-$level;
	else {
		$des=true;
	}

	if (($s = DBSiteInfo($_SESSION["usertable"]["contestnumber"],$_SESSION["usertable"]["usersitenumber"])) == null)
		ForceLoad("index.php");
	$score = DBScore($_SESSION["usertable"]["contestnumber"], $ver, $hor*60, $s["siteglobalscore"]);
    reset($score);
}



echo json_encode($score,JSON_PRETTY_PRINT);
?>
