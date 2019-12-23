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

		// Variable Heizprogramm erstellen
		$this->MaintainVariable('HeizProg', 'Heizprogramm', vtInteger, 'Heizungsautomatik', 1, true);
		
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
		
		
		//Wochenplan erstellen
		$this->RegisterEvent("Wochenplan Normal", "Wochenplan_".$this->InstanceID, 2, $this->InstanceID, 30);
		$this->RegisterEvent("Wochenplan Feiertag", "Wochenplan_Feiertag_".$this->InstanceID, 2, $this->InstanceID, 31);
        	
		// Anlegen der Daten für den Wochenplan Normal
        	IPS_SetEventScheduleGroup($this->GetIDForIdent("Wochenplan_".$this->InstanceID), 0, 31); //Mo - Fr (1 + 2 + 4 + 8 + 16)
		IPS_SetEventScheduleGroup($this->GetIDForIdent("Wochenplan_".$this->InstanceID), 1, 96); //Sa + So (32 + 64)
		
		// Anlegen der Daten für den Wochenplan Feiertag
		IPS_SetEventScheduleGroup($this->GetIDForIdent("Wochenplan_Feiertag_".$this->InstanceID), 2, 127); //Mo - So (1 + 2 + 4 + 8 + 16 + 32 + 64)
			
		// Anlegen Aktionen für Wochenplan Normal
		IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_".$this->InstanceID), 1, "Absenken", 0x000FF, "SRHC_AbsenkTemp(\$_IPS['TARGET']");  
        	IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_".$this->InstanceID), 2, "Grundwärme", 0xFF9900 , "SRHC_GrundTemp(\$_IPS['TARGET']");  
        	IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_".$this->InstanceID), 3, "Heizen", 0xFF0000, "SRHC_HeizTemp(\$_IPS['TARGET']");
		
		// Anlegen Aktionen für Wochenplan Feiertag
		IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_Feiertag_".$this->InstanceID), 1, "Absenken", 0x000FF, "SRHC_AbsenkTemp(\$_IPS['TARGET']");  
        	IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_Feiertag_".$this->InstanceID), 2, "Grundwärme", 0xFF9900 , "SRHC_GrundTemp(\$_IPS['TARGET']");  
        	IPS_SetEventScheduleAction($this->GetIDForIdent("Wochenplan_Feiertag_".$this->InstanceID), 3, "Heizen", 0xFF0000, "SRHC_HeizTemp(\$_IPS['TARGET']"); 
	}

	private function RegisterEvent($Name, $Ident, $Typ, $Parent, $Position)
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
			IPS_SetParent($EventID, $Parent);
			IPS_SetIdent($EventID, $Ident);
			IPS_SetName($EventID, $Name);
			IPS_SetPosition($EventID, $Position);
			IPS_SetEventActive($EventID, true);  
		}
	 }  
	
	public function AbsenkTemp()
	{
		//Letzten Sollwert speichern
		$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Absenktemperatur
		$AbsenkTemp = GetValue($this->GetIDForIdent('AbsenkTemp'));
		
		 // Absenktemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$AbsenkTemp);
		IPS_Sleep(50);
	}
	
	public function GrundTemp()
	{
		//Letzten Sollwert speichern
		$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Grundtemperatur
		$GrundTemp = GetValue($this->GetIDForIdent('GrundTemp'));
		
		 // Grundtemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$GrundTemp);
		IPS_Sleep(50);
	}
	
	public function HeizTemp()
	{
		//Letzten Sollwert speichern
		$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Heiztemperatur
		$HeizTemp = GetValue($this->GetIDForIdent('HeizTemp'));
		
		 // Heiztemperatur in Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$HeizTemp);
		IPS_Sleep(50);
	}
	
	public function AntrAuf()
	{
		//Letzten Sollwert speichern
		$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
		// Antrieb Auf
		$AntrAuf = GetValue($this->GetIDForIdent('AntrAuf'));
		
		 // Antrieb Aufrin Solltemperatur schreiben
		RequestAction($this->ReadPropertyInteger('SetTempID'),$AntrAuf);
		IPS_Sleep(50);
	}
	
	public function AntrZu()
	{
		//Letzten Sollwert speichern
		$update = $this->SetValue('LastSetTemp', GetValue($this->ReadPropertyInteger('SetTempID')));
		
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

		// Heizungsprogramm
		$HeizProgID = $this->GetIDForIdent('HeizProg'); 
		$HeizProg = GetValue($HeizProgID);

		 // Letzte Solltemperatur
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
		 
		// Modus
		$ModusID = $this->ReadPropertyInteger('ModID');
		$Modus = GetValue($ModusID);
 		
		// Fensterkontakt
		$WindowID = $this->ReadPropertyInteger('WindowID');
		$Window = GetValue($WindowID);

		 // Anwesenheit 
		$Presence = GetValue($this->ReadPropertyInteger('PresenceID'));

		 // Steuerungsautomatik
		If ($HeizProg == 0) //Automatic => Steuerung durch CCU
		{
			RequestAction($ModusID,0);
		} 
		else if ($HeizProg == 1) // Manuelle Steuerung durch IPS 
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
		else if ($HeizProg == 2)
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
		else if ($HeizProg == 3)
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

}
?>
