<?php
require_once("globals.php");
if(!isset($_SESSION["usertable"])) {
    $_SESSION["usertable"]["contestnumber"]=DBGetActiveContest()["contestnumber"];
    $_SESSION["usertable"]["usersitenumber"]=array_pop(DBAllSiteInfo(DBGetActiveContest()["contestnumber"]))["sitenumber"];
    $_SESSION["usertable"]["usertype"]="score";
}
if (($s = DBSiteInfo($_SESSION["usertable"]["contestnumber"],$_SESSION["usertable"]["usersitenumber"])) == null)
  ForceLoad("../index.php");
// if ($_SESSION["usertable"]["usertype"]!="judge" &&
//     $_SESSION["usertable"]["usertype"]!="admin") $ver=true;
// else $ver=false;
if($_SESSION["usertable"]["usertype"]=="score") $des=false;
else $des=true;
$ver=false;
// temp do carlinhos (placar de judge == placar de time)
//if ($_SESSION["usertable"]["usertype"]=="judge") $ver = true;
if ($s["currenttime"] >= $s["sitelastmilescore"] && $ver)
    echo "<br><center>Scoreboard frozen</center>";
require('scoretable_public.php');