<?

	class Sonos extends IPSModule
	{

		public function __construct($InstanceID)
		{
			//Never delete this line!
			parent::__construct($InstanceID);
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
			$this->RegisterPropertyString("IPAddress", "");
			
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			$this->RegisterScript("_Steuerung", "_Steuerung", '<?
      IPS_SetScriptTimer($_IPS[\'SELF\']  , 5);                  // ScriptTimer einschalten (auf 60 Sekunde setzen)
      switch ($_IPS["SENDER"])                                     // Ursache (Absender) des Triggers ermittlen
{
  case "TimerEvent":                                     // Timer hat getriggert

$s_radio = GetValueString(IPS_GetObjectIDByName(\'Radiotitel\', IPS_GetParent($_IPS[\'SELF\'])));
$loud = GetValue(IPS_GetObjectIDByName(\'Loudness\', IPS_GetParent($_IPS[\'SELF\'])));
$ip = IPS_GetProperty(IPS_GetParent($_IPS[\'SELF\']), "IPAddress");
if (Sys_Ping($ip, 1000) == true) {
include("PHPSonos.inc.php");
$sonos = new PHPSonos($ip); //Sonos ZP IPAdresse
$s_steuer = $sonos->GetTransportInfo();
SetValueInteger(IPS_GetObjectIDByName(\'Status\', IPS_GetParent($_IPS[\'SELF\'])) , $s_steuer);
//$s_sleep = $sonos->GetSleeptimer();
//SetValueString(IPS_GetObjectIDByName(\'Sleeptimer\', IPS_GetParent($_IPS[\'SELF\']))   , $s_sleep);

$loud = $sonos->GetLoudness();
if ($loud == 1)
{
  SetValueInteger(IPS_GetObjectIDByName(\'Loudness\', IPS_GetParent($_IPS[\'SELF\'])) , 1);
}
if ($loud == 0)
{
  SetValueInteger(IPS_GetObjectIDByName(\'Loudness\', IPS_GetParent($_IPS[\'SELF\'])) , 0);
}

$mute = $sonos->GetMute();
if ($mute == 1)
{
  SetValueInteger(IPS_GetObjectIDByName(\'Mute\', IPS_GetParent($_IPS[\'SELF\']))  , 1);
}
if ($mute == 0)
{
  SetValueInteger(IPS_GetObjectIDByName(\'Mute\', IPS_GetParent($_IPS[\'SELF\'])), 0);
}

$s_vol = $sonos->GetVolume();;
SetValueInteger(IPS_GetObjectIDByName(\'Volume\', IPS_GetParent($_IPS[\'SELF\'])) , $s_vol);

$s_bass = $sonos->GetBass();;
SetValueInteger(IPS_GetObjectIDByName(\'Bass\', IPS_GetParent($_IPS[\'SELF\'])) , $s_bass);

$s_treble = $sonos->GetTreble();;
SetValueInteger(IPS_GetObjectIDByName(\'Treble\', IPS_GetParent($_IPS[\'SELF\'])) , $s_treble);

// Titelanzeige
$posInfo3 = $sonos->GetPositionInfo();
//var_dump($posInfo3);
$posInfo2 = GetValueString(IPS_GetObjectIDByName(\'Radiotitel_alt\', IPS_GetParent($_IPS[\'SELF\']))  );
$posInfo = $posInfo3[\'streamContent\'];
$s_radio = $sonos->GetMediaInfo();
//$s_radio = $s_radio[\'title\'];

//var_dump($s_radio);
if (strlen($posInfo3[\'streamContent\']) != 0) // Für NAS Titel Anzeige

{
$s_radio = $s_radio[\'title\'];
}
else
{
$s_radio = "";
}

if ($s_radio == "") // FÃ¼r NAS Titel Anzeige
{
		$s_radio = $sonos->GetPositionInfo();
		$s_radio2 = $s_radio[\'title\'];
      $s_radio3 = $s_radio[\'artist\'];
      $array[0] = "$s_radio2";
	   $array[1] = "|";
	   $array[2] = "$s_radio3";
	   $posInfo = implode("", $array);
//echo ($s_radio);
$posInfo = utf8_decode($posInfo);
}

if ($posInfo == $posInfo2)
{

// mache nichts
}
else
{
$posInfo2=$posInfo;
SetValueString(IPS_GetObjectIDByName(\'Radiotitel\', IPS_GetParent($_IPS[\'SELF\'])) , $posInfo);
SetValueString(IPS_GetObjectIDByName(\'Radiotitel_alt\', IPS_GetParent($_IPS[\'SELF\'])) , $posInfo);
$laenge = strlen($posInfo);
}
} // if sys_ping
break;
}

?>');
			
			$this->RegisterProfileIntegerEx("Status.SONOS", "Information", "", "", Array(
				Array(0, "Prev", "", -1),
				Array(1, "Play", "", -1),
				Array(2, "Pause", "", -1),
				Array(3, "Stop", "", -1),
				Array(4, "Next", "", -1)
			));
			$this->RegisterProfileIntegerEx("Schalter.SONOS", "Information", "", "", Array(
				Array(0, "Off", "", 0xFF0000),
				Array(1, "On", "", 0x00FF00)
			));
			$this->RegisterProfileIntegerEx("Station.SONOS", "Information", "", "", Array(
				Array(0, "Radio Lippe", "", -1),
				Array(1, "FFN", "", -1),
				Array(2, "FFH", "", -1),
				Array(3, "OE3", "", -1),
				Array(4, "Antenne Bayern", "", -1),
				Array(5, "frei", "", -1)
			));
			$this->RegisterProfileInteger("Volume.SONOS", "Intensity", "", " %", 0, 100, 1);
			$this->RegisterProfileInteger("Klang.SONOS", "Intensity", "", " %", -10, 10, 1);
		
			$this->RegisterVariableInteger("Status", "Status", "Status.SONOS");
			$this->EnableAction("Status");
			$this->RegisterVariableInteger("Volume", "Volume", "Volume.SONOS");
			$this->EnableAction("Volume");
			$this->RegisterVariableInteger("Bass", "Bass", "Klang.SONOS");
			$this->EnableAction("Bass");
			$this->RegisterVariableInteger("Treble", "Treble", "Klang.SONOS");
			$this->EnableAction("Treble");
			$this->RegisterVariableInteger("Loudness", "Loudness", "Schalter.SONOS");
			$this->EnableAction("Loudness");
			$this->RegisterVariableInteger("Mute", "Mute", "Schalter.SONOS");
			$this->EnableAction("Mute");
			$this->RegisterVariableInteger("Station", "Station", "Station.SONOS");
			$this->EnableAction("Station");
			$this->RegisterVariableString("Radiotitel", "Radiotitel", "");
			$this->RegisterVariableString("Radiotitel_alt", "Radiotitel_alt", "");
		}

		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* SNS_Play($id);
		*
		*/
		public function Play()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Play();
			
		}
		
		public function Pause()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Pause();
			
		}
		public function Stop()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Stop();
			
		}
		
		public function Previous()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Previous();
			
		}
		
		public function Next()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Next();
			
		}
		
		public function SetVolume($volume)
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetVolume($volume);
			
		}
		public function SetBass($bass)
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetBass($bass);
			
		}
		public function SetTreble($treble)	
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetTreble($treble);
			
		}

		public function SetMute($mute)	
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetMute($mute);
			
		}

		public function SetLoudness($loudness)	
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetLoudness($loudness);
			
		}
		public function SetRadio($radio)	
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetRadio($radio);
			
		}

		
		
		public function RequestAction($Ident, $Value)
		{
			
			switch($Ident) {
				case "Station":
					switch($Value) {
						case 0: 
							$this->SetRadio("x-rincon-mp3radio://edge.live.mp3.mdn.newmedia.nacamar.net/ps-radiolippe/livestream.mp3", "Radio Lippe");
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 1: 
							$this->SetRadio("x-rincon-mp3radio://player.ffn.de/ffn.mp3", "FFN");
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 2: 
							$this->SetRadio("x-sonosapi-stream:s17490?sid=254&amp;flags=32", "FFH");
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 3: 
							$this->SetRadio("x-rincon-mp3radio://mp3stream7.apasf.apa.at:8000", "Oe3");
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 4: 
							$this->SetRadio("x-sonosapi-stream:s15030?sid=254&amp;flags=32", "Antenne Bayern");
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;

					}
					break;
				case "Status":
					switch($Value) {
						case 0: //Prev
							$this->Previous();
							break;
						case 1: //Play
							$this->Play();
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 2: //Pause
							$this->Pause();
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 3: //Stop
							$this->Stop();
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 4: //Next
							$this->Next();
							break;
					}
					break;
				case "Volume":
					$this->SetVolume($Value);
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case "Bass":
					$this->SetBass($Value);
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case "Treble":
					$this->SetTreble($Value);
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case "Mute":
					$this->SetMute($Value);
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case "Loudness":
					switch($Value) {
						case 0: //Off
    					$this->SetLoudness($Value);
    					SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 1: //On
    					$this->SetLoudness($Value);
    					SetValue($this->GetIDForIdent($Ident), $Value);
							break;
					}
					break;

				default:
					throw new Exception("Invalid ident");
			}
		
		}
		
		//Remove on next Symcon update
		protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
		
			if(!IPS_VariableProfileExists($Name)) {
				IPS_CreateVariableProfile($Name, 1);
			} else {
				$profile = IPS_GetVariableProfile($Name);
				if($profile['ProfileType'] != 1)
					throw new Exception("Variable profile type does not match for profile ".$Name);
			}
			
			IPS_SetVariableProfileIcon($Name, $Icon);
			IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
			
		}
		
		protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
		
			$this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $Associations[0][0], $Associations[sizeof($Associations)-1][0], 0);
		
			foreach($Associations as $Association) {
				IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}
			
		}
		
	
	}

?>
