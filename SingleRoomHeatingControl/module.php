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
			//HM_WriteValueBoolean($HM_Inst, 'AUTO_MODE',true);
		} 
		else if ($HeizProg == 1) // Manuelle Steuerung durch IPS 
		{
			If ($Presence == false)
			{
				//Letzte Sollwert schreiben
				$update = $this->SetValue('LastSetTemp', $SetTemp);

				// Auf Absenktemperatur stellen
				RequestAction($ModusID,1);
				RequestAction($SetTempID,$AbsenkTemp);
				//HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AbsenkTemp);
				IPS_Sleep(50);
			}
			Else if ($Presence == true)
			{
				// Auf letzten Sollwert stellen
				RequestAction($ModusID,1);
				RequestAction($SetTempID,$LastSetTemp);
				//HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$LastSetTemp);
				IPS_Sleep(50);
			}
		} 
		else if ($HeizProg == 2)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Stellantrieb Auf
			RequestAction($ModusID,1);
			RequestAction($SetTempID,$AntrAuf);
			//HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AntrAuf);
			IPS_Sleep(50);
		} 
		else if ($HeizProg == 3)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Stellantrieb Zu
			RequestAction($ModusID,1);
			RequestAction($SetTempID,$AntrZu);
			//HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AntrZu);
			IPS_Sleep(50);
		} 
	}

}
?>
