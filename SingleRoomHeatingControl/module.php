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
		$this->RegisterPropertyInteger('InstanceID', null);
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
		$win = GetValue($this->ReadPropertyInteger('WindowID'));

		 // Anwesenheit 
		$pres = GetValue($this->ReadPropertyInteger('PresenceID'));

		 // Stellantrieb Auf
		$AntrAuf = $this->ReadPropertyFloat('AntrAuf');

		// Stellantrieb Zu
		$AntrZu = $this->ReadPropertyFloat('AntrZu'); 

		// Absenktemperatur
		$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');

		// Solltemperatur
		$SetTemp = GetValue($this->ReadPropertyInteger('SetTempID'));
		$SetTempID = $this->ReadPropertyInteger('SetTempID'); 
		 
		 // Instanz ID
		 $HM_Inst = $this->ReadPropertyInteger('InstanceID');
		 
		 // Steuerungsautomatik
		If ($HeizProg == 0) //Automatic => Steuerung durch CCU
		{
			RequestAction(44935,0);
			//HM_WriteValueBoolean($HM_Inst, 'AUTO_MODE',true);
		} 
		else if ($HeizProg == 1) // Manuelle Steuerung durch IPS 
		{
			If ($pres == false)
			{
				//Letzte Sollwert schreiben
				$update = $this->SetValue('LastSetTemp', $SetTemp);

				// Auf Absenktemperatur stellen
				RequestAction(44935,1);
				RequestAction($SetTempID,$AbsenkTemp);
				//HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AbsenkTemp);
				IPS_Sleep(50);
			}
			Else if ($pres == true)
			{
				// Auf letzten Sollwert stellen
				//RequestAction($SetTempID,$LastSetTemp);
				HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$LastSetTemp);
				IPS_Sleep(50);
			}
		} 
		else if ($HeizProg == 2)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Stellantrieb Auf
			//RequestAction($SetTempID,$AntrAuf);
			HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AntrAuf);
			IPS_Sleep(50);
		} 
		else if ($HeizProg == 3)
		{
			//Letzten Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			
			// Stellantrieb Zu
			//RequestAction($SetTempID,$AntrZu);
			HM_WriteValueFloat($HM_Inst, 'MANU_MODE',$AntrZu);
			IPS_Sleep(50);
		} 
	}

}
?>
