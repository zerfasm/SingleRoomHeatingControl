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
		$this->RegisterPropertyFloat('AbsenkTemp', 19.0);
		$this->RegisterPropertyFloat('GrundTemp', 20.0);
		$this->RegisterPropertyFloat('HeizTemp', 22.0);
		$this->RegisterPropertyFloat('AntrAuf', 30.0);
		$this->RegisterPropertyFloat('AntrZu', 6.0);

		// Wochenplan
		$this->RegisterPropertyInteger('WeeklyTimeTableEventID', 0);
		$this->RegisterPropertyInteger('HolidayIndicatorID', 0);
		$this->RegisterPropertyInteger('DayUsedWhenHoliday',6);

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

		// Variable Letze Solltemperatur erstellen
		$this->MaintainVariable('LastSetTemp', 'Letzte Solltemperatur', vtFloat, '~Temperature', 2, true);
		
		// ID Instanz
		$Instance = $this->InstanceID;
		
		// Ausgelöstes Ereignis durch Fensterkontakt erstellen
		//$eid = IPS_CreateEvent(0);                  				//Ausgelöstes Ereignis
		//IPS_SetEventTrigger($eid, 1, $this->ReadPropertyInteger('WindowID'));   //Bei Änderung von Variable $WindowID
		//IPS_SetParent($eid, $Instance);         				//Ereignis zuordnen
		//IPS_SetEventActive($eid, true); 	    				//Ereignis aktiv setzen
		
		// Ausgelöstes Ereignis durch Heiprogramm erstellen
		//$HeizProgID = $this->GetIDForIdent('HeizProg');
		
		//$eid = IPS_CreateEvent(0);                  				//Ausgelöstes Ereignis
		//IPS_SetEventTrigger($eid, 1, $this->GetIDForIdent('HeizProg'));    	//Bei Änderung von Variable $HeizProgID
		//IPS_SetParent($eid, $Instance);         				//Ereignis zuordnen
		//IPS_SetEventActive($eid, true); 	    				//Ereignis aktiv setzen
	}

	public function AbsenkTemp()
	{
		// Absenktemperatur
		$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		RequestAction($SetTempID,$AbsenkTemp);
		IPS_Sleep(50);
	}
	
	public function GrundTemp()
	{
		// Grundtemperatur
		$GrundTemp = $this->ReadPropertyFloat('GrundTemp');
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		RequestAction($SetTempID,$GrundTemp);
		IPS_Sleep(50);
	}
	
	public function HeizTemp()
	{
		// Grundtemperatur
		$HeizTemp = $this->ReadPropertyFloat('HeizTemp');
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		RequestAction($SetTempID,$HeizTemp);
		IPS_Sleep(50);
	}
	
	public function AntrAuf()
	{
		// Grundtemperatur
		$AntrAuf = $this->ReadPropertyFloat('AntrAuf');
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		RequestAction($SetTempID,$AntrAuf);
		IPS_Sleep(50);
	}
	
	public function AntrZu()
	{
		// Grundtemperatur
		$AntrZu = $this->ReadPropertyFloat('AntrZu');
		
		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		RequestAction($SetTempID,$AntrZu);
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

		 // Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		$SetTemp = GetValue($SetTempID);
		 
		// Letzte SollTemperatur 
		$LastSetTemp = GetValue($this->GetIDForIdent('LastSetTemp'));
		 
		// Absenktemperatur
		$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');
		
		// Stellantrieb Auf
		$AntrAuf = $this->ReadPropertyFloat('AntrAuf');

		// Stellantrieb Zu
		$AntrZu = $this->ReadPropertyFloat('AntrZu'); 
		 
		// Modus
		$ModusID = $this->ReadPropertyInteger('ModID');
		$Modus = GetValue($ModusID);
 		
		// Fensterkontakt
		$WindowID =$this->ReadPropertyInteger('WindowID');
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
			Else if (($Presence == true) and ($Window == true))
			{
				// Modus auf Manuell stellen
				If ($Modus == 0)
				{
					RequestAction($ModusID,1);
				}

				// Auf letzten Sollwert stellen
				RequestAction($SetTempID,$AntrZu);
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
