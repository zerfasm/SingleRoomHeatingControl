<?php
	require_once __DIR__.'/../libs/traits.php';  // Allgemeine Funktionen

	class SingleRoomHeatingControl extends IPSModule {
		
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
			$this->RegisterPropertyBoolean('CreateHeizTemp', true);
						
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
			
			// Create Heizautomatik
			$create = $this->ReadPropertyBoolean('CreateHeizAuto');
        		$this->MaintainVariable('HeizAuto', 'Heizautomatik', vtFloat, 'Switch', 1, $create);
			
			// Create Heizprogramm
			$create = $this->ReadPropertyBoolean('CreateHeizProg');
        		$this->MaintainVariable('HeizProg', 'Heizprogramm', vtFloat, 'Heizungsautomatik', 2, $create);
							
			// Create Heiztemperatur
			$create = $this->ReadPropertyBoolean('CreateHeizTemp');
        		$this->MaintainVariable('HeizTemp', 'Heiztemperatur', vtFloat, '~Temperature', 3, $create);
			
		}
		
		 public function Update()
    		{
        		$result = 'Ergebnis konnte nicht ermittelt werden!';
        		// Daten lesen
       			 $state = true;
			
			// Fensterkontakt 
			$win = $this->ReadPropertyInteger('WindowID');
        		if ($win != 0) {
            			$win = GetValue($win);
        		} else {
            			$this->SendDebug('UPDATE', 'Window ID not set!');
            			$state = false;
        			}
			 
			 // Anwesenheit 
			$pres = $this->ReadPropertyInteger('PresenceID');
        		if ($pres != 0) {
            			$win = GetValue($pres);
        		} else {
            			$this->SendDebug('UPDATE', 'Presence ID not set!');
            			$state = false;
        			}
			
		}

	}
