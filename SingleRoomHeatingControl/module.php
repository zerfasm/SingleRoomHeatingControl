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
			
			// Time Schedule
			$this->RegisterPropertyInteger('WeeklyTimeTableEventID', 0);
			$this->RegisterPropertyInteger('HolidayIndicatorID', 0);
			$this->RegisterPropertyInteger('DayUsedWhenHoliday',6);
			
			// Contacts
			$this->RegisterPropertyInteger('WindowID', 0);
			
			// Presence
			$this->RegisterPropertyInteger('PresenceID', 0);
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
			
			// Create Temperatur Absenken
        		$this->MaintainVariable('AbsenkTemp', 'Absenken', vtFloat, '~Temperature', 1, true); 
			
			// Create Temperatur Grundwärme
        		$this->MaintainVariable('GrundTemp', 'Grundwärme', vtFloat, '~Temperature', 2, true); 
			
			// Create Temperatur Heizen
        		$this->MaintainVariable('HeizTemp', 'Heizen', vtFloat, '~Temperature', 3, true); 
			
			// Create Stellantrieb Auf
        		$this->MaintainVariable('AntrAuf', 'Stellantrieb Auf', vtFloat, '~Temperature', 4, true);
			
			// Create Stellantrieb Zu
        		$this->MaintainVariable('AntrZu', 'Stellantrieb Zu', vtFloat, '~Temperature', 5, true);			
			
		}

	}
