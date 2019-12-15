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
			$this->RegisterPropertyInteger('ActTempID', 0);
			$this->RegisterPropertyInteger('SetTempID', 0);
			$this->RegisterPropertyFloat('AbsenkTemp', 19.0);
			$this->RegisterPropertyFloat('GrundTemp', 20.0);
			$this->RegisterPropertyFloat('AntrAuf', 30.0);
			$this->RegisterPropertyFloat('AntrZu', 6.0);
			
			// Variablen
			$this->RegisterPropertyInteger('HeizProg', 0);
					
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
			$HeizProg = $this->ReadPropertyInteger('HeizProg');
			 
			// Fensterkontakt 
			$win = GetValue($this->ReadPropertyInteger('WindowID'));
			 
			 // Anwesenheit 
			$pres = $this->ReadPropertyInteger('PresenceID');
			 
			 // Absenktemperatur
			$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');
			 
			// Solltemperatur
			$SetTemp = GetValue($this->ReadPropertyInteger('SetTempID')); 

			//RequestAction($this->ReadPropertyInteger('SetTempID'),$SetTemp);
			 
			//Letzte Sollwert schreiben
			$update = $this->SetValue('LastSetTemp', $SetTemp);
			 
			 If ($HeizProg == 0)
			 {
			 HM_WriteValueFloat(52525, 'MANU_MODE',$AbsenkTemp);
			 }
			
		}

	}
