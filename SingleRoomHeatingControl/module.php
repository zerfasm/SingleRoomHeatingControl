<?php
require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen

class SingleRoomHeatingControl extends IPSModule 
{

	use ProfileHelper, DebugHelper;

	public function Create()
	{
		//Never delete this line!
		parent::Create();

		// Temperature Parameter
		$this->RegisterPropertyString('RoomName', "");
		$this->RegisterPropertyInteger('ModID', 0);
		$this->RegisterPropertyInteger('SetTempID', 0);
		$this->RegisterPropertyFloat('AbsenkTemp', 19.0);
		$this->RegisterPropertyFloat('GrundTemp', 20.0);
		$this->RegisterPropertyFloat('AntrAuf', 30.0);
		$this->RegisterPropertyFloat('AntrZu', 6.0);

		// Time Schedule
		$this->RegisterPropertyInteger('WeeklyTimeTableEventID', 0);
		$this->RegisterPropertyInteger('HolidayIndicatorID', 0);
		$this->RegisterPropertyInteger('DayUsedWhenHoliday',6);

		// Contacts
		$this->RegisterPropertyInteger('WindowID', 0);

		// Presence
		$this->RegisterPropertyInteger('PresenceID', 0);

		// Update trigger
		$this->RegisterTimer('UpdateTrigger', 0, "SRHC_Update(\$_IPS['TARGET']);");
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

		// Create Heizprogramm
		$this->MaintainVariable('HeizProg', 'Heizprogramm', vtInteger, 'Heizungsautomatik', 1, true);
		
		// Create Letzet Solltemperatur
		$this->MaintainVariable('LastSetTemp', 'Letzte Solltemperatur', vtFloat, '~Temperature', 2, true);

	}

	 public function Update()
	{
		$result = 'Ergebnis konnte nicht ermittelt werden!';
		// Daten lesen
		 $state = true;
		 
		// Heizungsprogramm 
		$HeizProg = GetValue($this->GetIDForIdent('HeizProg'));
		 
		// Letzte SollTemperatur 
		$LastSetTemp = GetValue($this->GetIDForIdent('LastSetTemp'));
		
		 // Fensterkontakt 
		$Window = GetValue($this->ReadPropertyInteger('WindowID'));

		 // Anwesenheit 
		$Presence = GetValue($this->ReadPropertyInteger('PresenceID'));

		 // Stellantrieb Auf
		$AntrAuf = $this->ReadPropertyFloat('AntrAuf');

		// Stellantrieb Zu
		$AntrZu = $this->ReadPropertyFloat('AntrZu'); 

		// Absenktemperatur
		$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');

		// Solltemperatur
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		$SetTemp = GetValue($SetTempID);
		
		 
		 // Modus
		 $ModusID = $this->ReadPropertyInteger('ModID');
		 $Modus = GetValue($ModusID);
		 
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
