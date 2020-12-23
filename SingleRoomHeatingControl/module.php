<?php
require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen
class SingleRoomHeatingControl extends IPSModule 
{
	use ProfileHelper, DebugHelper;
	public function Create()
	{
		//Never delete this line!
		parent::Create();
		
		// Temperatur Parameter
		$this->RegisterPropertyString('RoomName', "");
		$this->RegisterPropertyInteger('ModID', 0);
		$this->RegisterPropertyInteger('SetTempID', 0);
		
		// Fensterkontakt
		$this->RegisterPropertyInteger('WindowID', 0);
		
		// Anwesenheit
		$this->RegisterPropertyInteger('PresenceID', 0);
		
		// Anwesenheit
		$this->RegisterPropertyInteger('HolidayID', 0);
		
		// Wochenplan Normal
		$this->RegisterPropertyInteger('WeekplanID', 0);
		
		// Wochenplan Feiertag
		$this->RegisterPropertyInteger('WeekplanHolidayID', 0);
		
		// Update trigger
		$this->RegisterTimer('UpdateTrigger', 0, "SRHC_Update(\$_IPS['TARGET']);");
		
		// Absenkentemperatur trigger
		$this->RegisterTimer('AbsenkTempTrigger', 0, "SRHC_AbsenkTemp(\$_IPS['TARGET']);");
		
		// Grundtemperatur trigger
		$this->RegisterTimer('GrundTempTrigger', 0, "SRHC_GrundTemp(\$_IPS['TARGET']);");
		
		// Heiztemperatur trigger
		$this->RegisterTimer('HeizTempTrigger', 0, "SRHC_HeizTemp(\$_IPS['TARGET']);");
		
		// Antrieb Auf trigger
		$this->RegisterTimer('AntrAufTrigger', 0, "SRHC_AntrAuf(\$_IPS['TARGET']);");
		
		// Antrieb Zu trigger
		$this->RegisterTimer('AntrZuTrigger', 0, "SRHC_AntrZu(\$_IPS['TARGET']);");
		
		// Steuerungsmodus
		$this->RegisterPropertyInteger('SteuerMod', 0);
	}
	
	public function Destroy()
	{
		//Never delete this line!
		parent::Destroy();
	}
	
	public function ApplyChanges()
	{
		//Never delete this line!
		parent::ApplyChanges();	
		
		// Variable Steuerungsmodus erstellen
		$this->MaintainVariable('SteuerMod', 'Steuerungsmodus', vtInteger, 'Heizungsautomatik', 1, true);
		
		// Variable Absenktemperatur erstellen
		$this->MaintainVariable('AbsenkTemp', 'Absenktemperatur', vtFloat, '~Temperature.Room', 2, true);
		
		// Variable Grundwärme erstellen
		$this->MaintainVariable('GrundTemp', 'Grundwärme', vtFloat, '~Temperature.Room', 3, true);
		
		// Variable Heiztemperatur erstellen
		$this->MaintainVariable('HeizTemp', 'Heiztemperatur', vtFloat, '~Temperature.Room', 4, true);
		
		// Variable Letze Solltemperatur erstellen
		$this->MaintainVariable('LastSetTemp', 'Letzte Solltemperatur', vtFloat, '~Temperature.Room', 5, true);
		
		// Variable Stellantrieb Auf erstellen
		$this->MaintainVariable('AntrAuf', 'STA-Auf', vtFloat, '~Temperature.HM', 6, true);
		
		// Variable Stellantrieb Zu erstellen
		$this->MaintainVariable('AntrZu', 'STA-Zu', vtFloat, '~Temperature.HM', 7, true);	
		
		// ID Instanz
		$Instance = $this->InstanceID;
		
		// Trigger Fenster
		If ($this->ReadPropertyInteger('WindowID') > 0)
		{
			$this->RegisterTriggerWindow("Fenster", "TriggerFenster", 0, $Instance, 0,"SRHC_Update(\$_IPS['TARGET']);");
		};
		
		// Trigger Anwesenheit
		$this->RegisterTriggerPresence("Anwesenheit", "TriggerAnwesenheit", 0, $Instance, 0,"SRHC_Update(\$_IPS['TARGET']);");
		
		// Trigger Steuerungsmodus
		$this->RegisterTriggerSteuerMod("Steuerungsmodus", "TriggerSteuerMod", 0, $Instance, 0,"SRHC_Update(\$_IPS['TARGET']);");
	}
	
	public function AbsenkTemp()
	{
		//Letzten Sollwert speichern
		//$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Absenktemperatur
		$AbsenkTemp = GetValue($this->GetIDForIdent('AbsenkTemp'));
		
		// Absenktemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$AbsenkTemp);
		IPS_Sleep(50);
	}
	
	public function GrundTemp()
	{
		//Letzten Sollwert speichern
		//$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Grundtemperatur
		$GrundTemp = GetValue($this->GetIDForIdent('GrundTemp'));
		
		// Grundtemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$GrundTemp);
		IPS_Sleep(50);
	}
	
	public function HeizTemp()
	{
		//Letzten Sollwert speichern
		//$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Heiztemperatur
		$HeizTemp = GetValue($this->GetIDForIdent('HeizTemp'));
		
		// Heiztemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$HeizTemp);
		IPS_Sleep(50);
	}
	
	public function AntrAuf()
	{
		//Letzten Sollwert speichern
		//$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Antrieb Auf
		$AntrAuf = GetValue($this->GetIDForIdent('AntrAuf'));
		
		 // Antrieb Aufrin Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$AntrAuf);
		IPS_Sleep(50);
	}
	
	public function AntrZu()
	{
		//Letzten Sollwert speichern
		//$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Antrieb Auf
		$AntrZu = GetValue($this->GetIDForIdent('AntrZu'));
		
		 // Antrieb Aufrin Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$AntrZu);
		IPS_Sleep(50);
	}
		
	public function Update()
	{
		$result = 'Ergebnis konnte nicht ermittelt werden!';
		// Daten lesen
		 $state = true;
		 
		// Steuerungsmodus
		$SteuerModID = $this->GetIDForIdent('SteuerMod'); 
		$SteuerMod = GetValue($SteuerModID);
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		$SetTemp = GetValue($SetTempID);
		 
		// Letzte SollTemperatur 
		$LastSetTempID = $this->GetIDForIdent('LastSetTemp');
		$LastSetTemp = GetValue($LastSetTempID);
		 
		// Absenktemperatur
		$AbsenkTemp = GetValue($this->GetIDForIdent('AbsenkTemp'));
		
		// Stellantrieb Auf
		$AntrAuf = GetValue($this->GetIDForIdent('AntrAuf'));
		
		// Stellantrieb Zu
		$AntrZu = GetValue($this->GetIDForIdent('AntrZu')); 
		 
		// Betriebsmodus
		$ModusID = $this->ReadPropertyInteger('ModID');
		$Modus = GetValue($ModusID);
 		
		// Fensterkontakt
		$WindowID = $this->ReadPropertyInteger('WindowID');
		$Window = GetValue($WindowID);
		
		 // Anwesenheit 
		$Presence = GetValue($this->ReadPropertyInteger('PresenceID'));
		
		 // Steuerungsautomatik
		If ($SteuerMod == 0) //Automatic => Steuerung durch CCU
		{
			RequestAction($ModusID,0);
		} 
		else if ($SteuerMod == 1) // Manuelle Steuerung durch IPS 
		{
			If ($Presence == false)
			{
				//Letzten Sollwert speichern
				$update = $this->SetValue('LastSetTemp', $SetTemp);
				
				// Modus auf Manuell stellen
				If ($Modus == 0)
				{
					RequestAction($ModusID,1);
				}
				
				// Auf Absenktemperatur stellen
				RequestAction($SetTempID,$AbsenkTemp);
				IPS_Sleep(50);
			}
			Else if (($Presence == true) and ($Window == true))
			{
				// Modus auf Manuell stellen
				If ($Modus == 0)
				{
					RequestAction($ModusID,1);
				}
				
				//Letzten Sollwert speichern
				$update = $this->SetValue('LastSetTemp', $SetTemp);
				IPS_Sleep(50);
				
				// Auf Sollwert Antrieb Zu stellen
				RequestAction($SetTempID,$AntrZu);
				IPS_Sleep(50);
			}
			Else if (($Presence == true) and ($Window == false))
			{
				// Modus auf Manuell stellen
				If ($Modus == 0)
				{
					RequestAction($ModusID,1);
				}
				
				// Auf letzten Sollwert stellen
				RequestAction($SetTempID,$LastSetTemp);
				IPS_Sleep(50);
			}
		} 
		else if ($SteuerMod == 2)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Modus auf Manuell stellen
			If ($Modus == 0)
			{
				RequestAction($ModusID,1);
			}
			
			// Stellantrieb Auf
			RequestAction($SetTempID,$AntrAuf);
			IPS_Sleep(50);
		} 
		else if ($SteuerMod == 3)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Modus auf Manuell stellen
			If ($Modus == 0)
			{
				RequestAction($ModusID,1);
			}
			
			// Stellantrieb Zu
			RequestAction($SetTempID,$AntrZu);
			IPS_Sleep(50);
		} 
	}
	
	private function RegisterTriggerWindow($Name, $Ident, $Typ, $Parent, $Position, $Skript)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
			$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
			IPS_DeleteEvent($eid);
			$eid = 0;
		}
		
		//we need to create one
		if ($eid == 0) {
		    $EventID = IPS_CreateEvent($Typ);
			IPS_SetEventTrigger($EventID, 1, $this->ReadPropertyInteger('WindowID'));
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventScript($EventID, $Skript); 
			IPS_SetEventActive($EventID, true);  
		}
	}
	
	private function RegisterTriggerPresence($Name, $Ident, $Typ, $Parent, $Position, $Skript)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
			$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
			IPS_DeleteEvent($eid);
			$eid = 0;
		}
		
		//we need to create one
		if ($eid == 0) {
		    $EventID = IPS_CreateEvent($Typ);
			IPS_SetEventTrigger($EventID, 1, $this->ReadPropertyInteger('PresenceID'));
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventScript($EventID, $Skript); 
			IPS_SetEventActive($EventID, true);  
		}
	}
	
	private function RegisterTriggerSteuerMod($Name, $Ident, $Typ, $Parent, $Position, $Skript)
	{
		$eid = @$this->GetIDForIdent($Ident);
		if($eid === false) {
			$eid = 0;
		} elseif(IPS_GetEvent($eid)['EventType'] <> $Typ) {
			IPS_DeleteEvent($eid);
			$eid = 0;
		}
		
		//we need to create one
		if ($eid == 0) {
		    $EventID = IPS_CreateEvent($Typ);
			IPS_SetEventTrigger($EventID, 1, $this->GetIDForIdent('SteuerMod'));
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventScript($EventID, $Skript); 
			IPS_SetEventActive($EventID, true);  
		}
	}
	
	/****************************************************************************
	GetWeekplanState,
	  liest den Zustand eines gewünschten Wochenplanereignisses aus

	  GetWeekplanStateGetWeekplanState(WochenplanID,[Abfragezeitpunkt_als_Systemzeit])

	Example: $Zustand = GetWeekplanState(1234);
		 oder
		 $Zustand = GetWeekplanStateGetWeekplanState(1234,time()-24*3600)

	[ActionID] => 4                          ==> Aktiver Zustand zum Abfragezeitpunkt
	[ActionName] => FREI                     ==> Zustandsbezeichnung zum Abfragezeitpunkt
	[CheckSysTime] => 1423986592             ==> Zeitpunkt fuer den die Ueberprufung gestartet wurde
	[CheckTime] => 15.02.2015 08:49:52       ==> Formatierter Ueberpruefungszeitpunkt
	[StartSysTime] => 1423861200             ==> Schaltpunkt wo der AKTIVE ZUSTAND aktiv wurde
	[StartTime] => 13.02.2015 22:00:00       ==> Formatierter Startpunkt
	[EndSysTime] => 1424034000               ==> Schaltpunkt wo der AKTIVE ZUSTAND verlassen wird
	[EndTime] => 15.02.2015 22:00:00         ==> Formatierter Endpunkt
	[Periode] => 172800                      ==> Zeitdauer des aktiven Zustand ins Sekunden
	[PeriodeHours] => 48                     ==> Formatierte Zeitdauer STUNDEN
	[PeriodeMinutes] => 0                    ==> Formatierte Zeitdauer MINUTEN
	[PeriodeSeconds] => 0                    ==> Formatierte Zeitdauer SEKUNDEN
	[PreviousActionID] => 2                  ==> Zustand der VOR dem aktuellen Zustand
	[PreviousActionName] => 2-Mittelschicht  ==> Zustandsbezeichung vorherigen Zustand
	[NextActionID] => 3                      ==> Zustand der den aktuellen Zustand beloest wird
	[NextActionName] => 3-Nachtschicht       ==> Zustandsbezeichung zukuenftiges Zustandes
	[WeekPlanID] => 15405                    ==> ID des Wochenplans
	[WeekPlanName] => SCHICHTPLANTEST        ==> Name des Wochenplans
	[WeekPlanActiv] => 1                     ==> Zustand ob der Wochenplan aktiv ist oder nicht 

	ReleaseNotes:
	15.02.2015 tgusi74
	 + Function erstellt und geprüft
	14.05.2015 tgusi74 #1
	 + Fehler beseitigt mit Schaltpunkt um 00:00:00 am aktuellen Tag
	15.05.2015 tgusi74 #2
	 + Fehler beseitigt mit Schaltpunkt um 00:00:00 am Folgetag
	28.12.2015 tgusi74 #3
	 + Variablen frueher initialisiert
	 + Wenn kein Tagesevent vorhanden, dann default 0
	31.12.2015 tgusi74 #4
	 + Diverse Aenderungen wegen ID=0 im Wochenplan
	 + WeekPlanName, WeekPlanID, WeekPlanName hinzugefuegt
	02.01.2016 tgusi74 #5
	 + Komplettumbau der Funktion wegen diverser Fehler mit ID=0
	06.01.2015 tgusi74 #6
	 + Ausgeblendete Tage (Luecken) haben nicht funktioniert, 
	   daher jetzt gesondert pruefen ==> DayFound
	*****************************************************************************/

	function GetWeekplanState($ID, $SysTimePoint=NULL, $CheckOnlySlot=false)
	 {
	   if($SysTimePoint == NULL)
	     {
	      $SysTimePoint = time();
	     }

	   $State = array();
	   $State['ActionID']           = 0;
	   $State['ActionName']         = "";
	   $State['CheckSysTime']       = $SysTimePoint;
	   $State['CheckTime']          = "01.01.1970 00:00:00";
	   $State['StartSysTime']       = $SysTimePoint-86400*7;
	   $State['StartTime']          = "01.01.1970 00:00:00";
	   $State['EndSysTime']         = $SysTimePoint+86400*7;
	   $State['EndTime']            = "01.01.1970 00:00:00";
	   $State['Periode']            = 0;
	   $State['PeriodeHours']       = 0;
	   $State['PeriodeMinutes']     = 0;
	   $State['PeriodeSeconds']     = 0;
	   $State['PreviousActionID']   = 0;
	   $State['PreviousActionName'] = "";
	   $State['NextActionID']       = 0;
	   $State['NextActionName']     = "";
	   $State['WeekPlanID']         = $ID;
	   $State['WeekPlanName']       = IPS_GetName($ID);
	   $State['WeekPlanActiv']      = 0;

	   $e = IPS_GetEvent($ID);
	   if ($e['EventType'] != 2)
	      {
	       echo ("Bei der ID= " . $ID . " handelt es sich um keinen Wochenplan !! ==> ABBRUCH !!" . "\r\n");
	       return($State);
	      }

	   if ($e['EventActive'] == 1)
	      {
	       //#4 - Aktivstatus mitausgeben
	       $State['WeekPlanActiv'] = 1;
	      }

	   //#3 Variablen vorinitialisieren
	   $StartPointFound = false;
	   $EndPointFound   = false;
	   $DayEventFound   = false;
	   $DayFound        = false;

	   //Durch alle Gruppen gehen
	   foreach($e['ScheduleGroups'] as $g)
	     {
	      //pruefen ob Gruppe fuer Zeitpunkt zustaendig
	      if(($g['Days'] & pow(2,date("N",$SysTimePoint)-1)) > 0)
		{
		 $DayFound        = true;
		 $StartPointFound = false;
		 $ActualSlotFound = false;
		 $EndPointFound   = false;
		 $State['StartSysTime'] = mktime(0,0,0,date("m",$SysTimePoint),date("d",$SysTimePoint),date("Y",$SysTimePoint));
		 $SearchTimeActDay      = date("H",$SysTimePoint) * 3600 + date("i",$SysTimePoint) * 60 + date("s",$SysTimePoint);

		 //Aktuellen Schaltpunkt suchen --> Wir nutzen die Eigenschaft, dass die Schaltpunkte immer aufsteigend sortiert sind.
		 foreach($g['Points'] as $p)
		   {
		    $StartTimeActDaySlot  = $p['Start']['Hour'] * 3600 + $p['Start']['Minute'] * 60 + $p['Start']['Second'];
		    $DayEventFound = true;

		    if($SearchTimeActDay >= $StartTimeActDaySlot)
		      {
		       if($ActualSlotFound == false)
			 {
			  $ActualSlotFound = true;
			  $State['ActionID']     = $p['ActionID'];
			  $State['StartSysTime'] = mktime($p['Start']['Hour'],$p['Start']['Minute'],$p['Start']['Second'],date("m",$SysTimePoint),date("d",$SysTimePoint),date("Y",$SysTimePoint));
			 }
		       else
			 {
			  $StartPointFound = true;
			  $State['PreviousActionID']  = $State['ActionID'];
			  $State['ActionID']          = $p['ActionID'];
			  $State['StartSysTime'] = mktime($p['Start']['Hour'],$p['Start']['Minute'],$p['Start']['Second'],date("m",$SysTimePoint),date("d",$SysTimePoint),date("Y",$SysTimePoint));
			 }
		      }
		    else
		      {
		       if($EndPointFound == false)
			 {
			  $EndPointFound   = true;
			  $State['NextActionID']     = $p['ActionID'];
			  $State['EndSysTime'] = mktime($p['Start']['Hour'],$p['Start']['Minute'],$p['Start']['Second'],date("m",$SysTimePoint),date("d",$SysTimePoint),date("Y",$SysTimePoint));
			 }
		       else
			 {
			  break;
			 }
		      }
		   }
		 break; //Sobald wir unseren Tag gefunden haben, können wir die Schleife abbrechen.
			//Jeder Tag darf nur in genau einer Gruppe sein.
		}
	     }

	     //wenn kein Tag gefunden wird ==> Tag ist ausgeblendet !!
	     if($DayFound == false)
	       {
		if($CheckOnlySlot == false)
		  {
		   for($i=0;$i<=7;$i++)
		      {
		       foreach($e['ScheduleGroups'] as $g)
			  {
			   //pruefen ob Gruppe fuer Zeitpunkt zustaendig
			   if(($g['Days'] & pow(2,date("N",$SysTimePoint)-1-$i)) > 0)
			     {
			      $DayFound=true;
			      break 2;
			     }
			  }
		      }

		   $State['StartSysTime'] = mktime(00,00,00,date("m",$SysTimePoint),date("d",$SysTimePoint)+1-$i,date("Y",$SysTimePoint));
		  }

		for($i=0;$i<=6;$i++)
		   {
		    foreach($e['ScheduleGroups'] as $g)
		       {
			//pruefen ob Gruppe fuer Zeitpunkt zustaendig
			if(($g['Days'] & pow(2,date("N",$SysTimePoint)-1+$i)) > 0)
			  {
			   $DayFound=true;
			   break 2;
			  }
		       }
		   }

		if($State['StartSysTime'] <= $SysTimePoint) 
		  {
		   $State['StartSysTime']=$SysTimePoint;
		  }

		$State['CheckSysTime']   = mktime(00,00,00,date("m",$SysTimePoint),date("d",$SysTimePoint)-1+$i,date("Y",$SysTimePoint));
		$State['EndSysTime']     = mktime(00,00,00,date("m",$SysTimePoint),date("d",$SysTimePoint)-1+$i,date("Y",$SysTimePoint));
		$EndPointFound = false;
	       }

	  if($CheckOnlySlot == false)
	    {

	     //Startpunkt wurde zwar gefunden aber die ActionID ist 0 --> vorigen Schaltpunkt suchen
	     if( ($StartPointFound == true) && ($State['ActionID'] == 0) )
	       {
		do{
		   $prevevent1 = GetWeekplanState($ID, $State['StartSysTime']-1 ,true);

		   $State['StartSysTime']     = $prevevent1['StartSysTime'];
		   $State['PreviousActionID'] = $prevevent1['ActionID'];

		   if ( ($State['ActionID'] == 0) && ($prevevent1['ActionID'] != 0) )
		      {
		       $State['StartSysTime']     = $prevevent1['StartSysTime'];
		       $State['ActionID']         = $prevevent1['ActionID'];
		       $State['PreviousActionID'] = $prevevent1['PreviousActionID'];
		       $DayEventFound = true;
		      }
		  } while(  ($State['ActionID'] == 0) && ($prevevent1['StartSysTime'] >= ($State['CheckSysTime']-86400*7)) );

		//Jetzt auch nochmals checken ob sich nicht auch der vorherige zu 0 veraendert hat
		if($State['PreviousActionID'] == 0)
		  {
		   $CheckTime1 = $State['StartSysTime'];

		   do{
		      $prevevent1 = GetWeekplanState($ID, $CheckTime1-1 ,true);

		      $CheckTime1  = $prevevent1['StartSysTime'];

		      if( ($State['PreviousActionID'] == 0) && ($prevevent1['ActionID'] != 0) )
			{
			 $State['PreviousActionID'] = $prevevent1['ActionID'];
			 $DayEventFound = true;
			}
		     } while(  ($State['PreviousActionID'] == 0) && ($prevevent1['StartSysTime'] >= ($State['CheckSysTime']-86400*14)) ); //hier geht auch 7

		   }
	    }

	    //Startpunkt liegt an einen der Vortage !!
	    if($StartPointFound == false)
	      {
	       do{
		  $prevevent1 = GetWeekplanState($ID, $State['StartSysTime']-1 ,true);

		  if ( ($prevevent1['ActionID'] == 0) && ($prevevent1['PreviousActionID'] == 0) && ($prevevent1['NextActionID'] == 0) )
		     {
		      $State['StartSysTime'] = mktime(00,00,00,date("m",$prevevent1['StartSysTime']),date("d",$prevevent1['StartSysTime']),date("Y",$prevevent1['StartSysTime']));
		     }
		  elseif ( ($prevevent1['NextActionID'] == 0) && ($prevevent1['PreviousActionID'] == 0) && ($State['ActionID'] == $prevevent1['ActionID']) )
		     {
		      $State['StartSysTime'] = mktime(00,00,00,date("m",$prevevent1['StartSysTime']),date("d",$prevevent1['StartSysTime']),date("Y",$prevevent1['StartSysTime']));
		     }
		  else
		     {
		      $State['StartSysTime']     = $prevevent1['StartSysTime'];
		      $State['PreviousActionID'] = $prevevent1['ActionID'];

		      if( ($State['ActionID'] == 0) && ($prevevent1['ActionID'] != 0) )
			{
			 $State['StartSysTime']     = $prevevent1['EndSysTime']; //??
			 $State['ActionID']         = $prevevent1['ActionID'];
			 $State['PreviousActionID'] = $prevevent1['PreviousActionID'];
			 if($prevevent1['NextActionID'] == 0)
			   {
			    $State['StartSysTime'] = $prevevent1['StartSysTime'];
			   }

			 $DayEventFound = true;
			}
		     }
		  } while( ( ($State['ActionID'] == 0) || ($State['ActionID'] == $prevevent1['PreviousActionID']) )&& ($prevevent1['StartSysTime'] >= ($State['CheckSysTime']-86400*14)) );


		  //Checken ob nicht doch der vorherige Schaltpunkt jetzt 0 ist
		  if($State['PreviousActionID'] == 0)
		    {
		     $CheckTime1 = $State['StartSysTime'];

		     do{
			$prevevent1 = GetWeekplanState($ID, $CheckTime1-1 ,true);
			if ( ($prevevent1['ActionID'] == 0) && ($prevevent1['PreviousActionID'] == 0) && ($prevevent1['NextActionID'] == 0) )
			   {
			    $CheckTime1 = mktime(00,00,00,date("m",$prevevent1['StartSysTime']),date("d",$prevevent1['StartSysTime']),date("Y",$prevevent1['StartSysTime']));
			   }
			else
			   {
			    $CheckTime1  = $prevevent1['StartSysTime'];
			   }

			if ( ($State['PreviousActionID'] == 0) && ($prevevent1['ActionID'] != 0) )
			   {
			    $State['PreviousActionID'] = $prevevent1['ActionID'];
			    $DayEventFound = true;
			   }
		       } while(  ($State['PreviousActionID'] == 0) && ($prevevent1['StartSysTime'] >= ($State['CheckSysTime']-86400*14)) );
		    }
	      }

	    //Vorheriger Schaltpunkt hat selben Status wie ActionID --> somit vorherigen Schaltpunkt fuer VORGAENGER suchen
	    if ($State['ActionID'] == $State['PreviousActionID'])
	       {
		$CheckTime1 = $State['StartSysTime'];

		do{
		   $prevevent1 = GetWeekplanState($ID, $CheckTime1-1 ,true);

		   if( ($prevevent1['ActionID'] == 0) && ($prevevent1['PreviousActionID'] == 0) && ($prevevent1['NextActionID'] == 0) )
		     {
		      $CheckTime1 = mktime(00,00,00,date("m",$prevevent1['StartSysTime']),date("d",$prevevent1['StartSysTime']),date("Y",$prevevent1['StartSysTime']));
		     }
		   elseif( ($prevevent1['NextActionID'] == 0) && ($prevevent1['PreviousActionID'] == 0) )
		     {
		      $CheckTime1 = mktime(00,00,00,date("m",$prevevent1['StartSysTime']),date("d",$prevevent1['StartSysTime']),date("Y",$prevevent1['StartSysTime']));
		     }
		   else
		     {
		      $CheckTime1 = $prevevent1['StartSysTime'];

		      if( ($State['PreviousActionID'] != $prevevent1['ActionID']) && ($prevevent1['ActionID'] != 0) )
			{
			 $State['PreviousActionID'] = $prevevent1['ActionID'];
			 $State['StartSysTime']     = $prevevent1['EndSysTime'];
			 $DayEventFound = true;
			}
		     }
		   } while(  ($State['PreviousActionID'] == $State['ActionID']) && ($prevevent1['StartSysTime'] >= ($State['CheckSysTime']-86400*7)) );
	       }


	    //Endpunkt wurde zwar gefunden aber der naechste Schaltpunkt ist 0
	    if( ($EndPointFound==true) && ($State['NextActionID'] == 0) )
	      {
	       $CheckTime1 = $State['EndSysTime'];

	       do{
		  $nextevent1 = GetWeekplanState($ID, $CheckTime1,true);

		  if( ($nextevent1['ActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) && ($nextevent1['NextActionID'] == 0) )
		    {
		     $CheckTime1 = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		    }
		 elseif( ($nextevent1['NextActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) )
		    {
		     $CheckTime1 = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		    }
		 else
		    {
		     $CheckTime1 = $nextevent1['EndSysTime'];

		     if( ($State['NextActionID'] != $nextevent1['ActionID']) || ($nextevent1['ActionID'] != 0) )
		       {
			$State['NextActionID']  = $nextevent1['ActionID'];
			$State['EndSysTime']    = $nextevent1['StartSysTime'];
			$DayEventFound = true;
		       }
		    }
		 } while( ($State['NextActionID'] == 0) && ($State['EndSysTime'] <= ($State['CheckSysTime']+86400*7)) );
	      }



	    //Endpunkt liegt an einen der Folgetage !!
	    if($EndPointFound==false)
	      {
	       if(($State['StartSysTime']+86400*6) < $State['CheckSysTime'])
		 {
		  $State['CheckSysTime'] = ($State['StartSysTime']+86400*6);
		 }

	       $State['EndSysTime'] = mktime(0,0,0,date("m",$State['CheckSysTime']),date("d",$State['CheckSysTime'])+1,date("Y",$State['CheckSysTime']));

	       do{
		  $nextevent1 = GetWeekplanState($ID, $State['EndSysTime'],true);

		  if( ($nextevent1['ActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) && ($nextevent1['NextActionID'] == 0) )
		    {
		     $State['EndSysTime'] = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		    }
		  elseif( ($nextevent1['NextActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) )
		    {
		     $State['EndSysTime'] = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		    }
		  else
		    {
		     $State['EndSysTime']   = $nextevent1['StartSysTime'];
		     $State['NextActionID'] = $nextevent1['ActionID'];

		     if( ($State['NextActionID'] == 0) || ($State['ActionID'] == $nextevent1['ActionID']) )
		       {
			$State['EndSysTime']     = $nextevent1['EndSysTime'];
			$DayEventFound = true;
		       }
		    }
		  } while( ( ($State['ActionID'] == $nextevent1['ActionID']) || ($State['NextActionID']  == $nextevent1['NextActionID']) )&& ($State['EndSysTime'] <= ($State['CheckSysTime']+86400*7)) );


	       //Wenn es kein Abloesezeitpunkt (nur ein Event im Wochenplan) gibt !!
	       if( ($State['EndSysTime'] >= ($State['CheckSysTime']+86400*7)) && ($State['NextActionID']== 0) )
		 {
		  $State['NextActionID'] = $State['ActionID'];
		 }


	       //Wenn naechster Schaltpunkt = 0 ist --> Folgeevent suchen !!
	       if( ($State['NextActionID'] == 0) && ($State['ActionID'] != 0) && ($State['PreviousActionID'] != 0) )
		 {
		  $CheckTime1 = mktime(0,0,0,date("m",$State['CheckSysTime']),date("d",$State['CheckSysTime'])+1,date("Y",$State['CheckSysTime']));

		  do{
		     $nextevent1 = GetWeekplanState($ID, $CheckTime1,true);

		     if( ($nextevent1['ActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) && ($nextevent1['NextActionID'] == 0) )
		       {
			$CheckTime1 = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		       }
		     elseif( ($nextevent1['NextActionID'] == 0) && ($nextevent1['PreviousActionID'] == 0) )
		       {
			$CheckTime1 = mktime(0,0,0,date("m",$nextevent1['StartSysTime']),date("d",$nextevent1['StartSysTime'])+1,date("Y",$nextevent1['StartSysTime']));
		       }
		     else
		       {
			$CheckTime1     = $nextevent1['EndSysTime'];

			if( ($State['NextActionID'] != $nextevent1['ActionID']) || ($nextevent1['ActionID'] != 0) )
			  {
			   $State['NextActionID']  = $nextevent1['ActionID'];
			   $State['EndSysTime']    = $nextevent1['StartSysTime'];
			   $DayEventFound = true;
			  }
		       }
		    } while( ($State['NextActionID'] == 0) && ($State['EndSysTime'] <= ($State['CheckSysTime']+86400*7)) );
		 }


	       //Wenn alle 3 Schaltpunkte gleich sind, dann Ueberpruefungszeitpunkt zurueckliefern (Kein oder nur EIN Event im Wochenplan)
	       if( ($State['ActionID'] == $State['PreviousActionID']) && ($State['ActionID'] == $State['NextActionID']) )
		 {
		  $State['StartSysTime'] = $SysTimePoint;
		  $State['EndSysTime']   = $SysTimePoint;
		 }


	      }
	    }



	  $State['CheckSysTime']   = $SysTimePoint;
	  $State['CheckTime']      = date("d.m.Y H:i:s",$State['CheckSysTime']);
	  $State['StartTime']      = date("d.m.Y H:i:s",$State['StartSysTime']);
	  $State['EndTime']        = date("d.m.Y H:i:s",$State['EndSysTime']);

	  $State['Periode']        = $State['EndSysTime'] - $State['StartSysTime'];
	  $State['PeriodeHours']   = floor($State['Periode'] / 3600);
	  $State['PeriodeMinutes'] = $State['Periode']       / 60   % 60;
	  $State['PeriodeSeconds'] = $State['Periode']              % 60;

	  foreach($e['ScheduleActions'] as $n)
	    {
	     if ($n['ID'] == $State['ActionID'])
		{
		 $State['ActionName'] = $n['Name'];
		}

	     if($n['ID'] == $State['PreviousActionID'])
	       {
		$State['PreviousActionName'] = $n['Name'];
	       }

	     if($n['ID'] == $State['NextActionID'])
	       {
		$State['NextActionName'] = $n['Name'];
	       }
	    }

	   return($State);
	 }
}
?>
