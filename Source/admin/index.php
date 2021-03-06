<?
session_start();

include("../inc/config.php");
include("../inc/tvswebsys.class.php");
include("../inc/unifi.class.php");
include("../inc/Mobile_Detect.php");

$unifiman		=	new UNIFI_CONTROL($unifiurl,$unifiuser,$unifipass,$maxusertime);
$tvswebsys	=	new TVSWEB_CONTROL($dbhost,$dbuser,$dbpass,$dbdb);
$detect		=	new Mobile_Detect();

$pagetpl	=	TVSWEB_CONTROL::LoadTemplate("base");

if(isset($_REQUEST["action"]))	{
	switch($_REQUEST["action"])	{
		case "login":
			$user		=		$_POST["username"];
			$pass		=		$_POST["password"];
			if($tvswebsys->CheckUser($user,$pass))	{
				$data		=	$tvswebsys->GetUserData($user);
				if($data["level"] > 1)	{
					$logtime	=	time();	
					$_SESSION["username"] = $user;
					$_SESSION["logged"] = True;
					unset($data["password"]);
					$_SESSION["userdata"] = $data;
					header("Location: /guest/admin");
				}else
					$loginmsg	=	"<font color=\"RED\"><B>Erro de autentica&ccedil;&atilde;o:</B>Voc&ecirc; n&atilde;o tem privil&eacute;gios para isso!</font>";
			}else
				$loginmsg	=	"<font color=\"RED\"><B>Erro de autentica&ccedil;&atilde;o:</B> Usu&aacute;rio ou Senha inv&aacute;lidos!</font>";
		break;
	}
}



$replacetags	=	array(	
	"TITLE"			=>	$title,
	"COMPANY"		=>	$company,
	"MSG"			=>	"",
	"USERNAME"		=>	$_REQUEST["username"],
	"NAME"			=>	$_SESSION["userdata"]["name"],
	"EMAIL"			=>	$_SESSION["userdata"]["email"],
	"LOGO"			=>	$logourl,
	"CUR_YEAR"		=>	date("Y"),
	"CUR_MONTH"		=>	date("n"),
	"PAGE_TITLE"		=>	"",
	"DEBUG"			=>  ""
);

$pagecont = $_REQUEST["m"];

switch($pagecont)	{
	case "login":
		$logintpl				=	TVSWEB_CONTROL::LoadTemplate("login");
		$page 				=	str_ireplace("{CONTENT}",$logintpl, $pagetpl);
		$replacetags["MSG"] 	=	$loginmsg;
		$replacetags["PAGE_TITLE"] = "Login";
		break;
	case "logout":
		unset($_SESSION["username"]);
		unset($_SESSION["userdata"]);
		unset($_SESSION["logged"]);
		header("Location: /guest/admin");
		break;
	case "emails":
		$emailtpl				=	TVSWEB_CONTROL::LoadTemplate("email");
		$maildata				=	$tvswebsys->BuildEmailList();
		$page				=	str_ireplace("{CONTENT}",$emailtpl,$pagetpl);
		$replacetags["PAGE_TITLE"] = "Emails";	
		$replacetags["MAILLIST"] = $maildata;	
		break;
	case "emailsexclusive":
		$emailtpl				=	TVSWEB_CONTROL::LoadTemplate("email");
		$maildata				=	$tvswebsys->BuildEmailList(True);
		$page				=	str_ireplace("{CONTENT}",$emailtpl,$pagetpl);
		$replacetags["PAGE_TITLE"] = "Emails";	
		$replacetags["MAILLIST"] = $maildata;	
		break;
	case "statistics":
		$statisticstpl				=	TVSWEB_CONTROL::LoadTemplate("statistics");
		$page	=	str_ireplace("{CONTENT}",$statisticstpl,$pagetpl);
		$replacetags["PAGE_TITLE"] = "Estat&iacute;sticas de Uso";
		break;
	default:
		if($_SESSION["logged"] == True)	{
			$admintpl	=	TVSWEB_CONTROL::LoadTemplate("admin");
			$page 	=	str_ireplace("{CONTENT}",$admintpl, $pagetpl);
		}else
			header("Location: /guest/admin/?m=login");
}

foreach($replacetags as $tag => $val)	
	$page	=	str_ireplace("{".$tag."}",$val,$page);

echo $page;

