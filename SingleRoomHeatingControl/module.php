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
			$this->RegisterPropertyBoolean('CreateAbsentSetTemp', true);
			$this->RegisterPropertyBoolean('CreateWindowSetTemp', true);
			
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
			
			// Create Absent Set Temperature
        		//$create = $this->ReadPropertyBoolean('CreateAbsentSetTemp');
			$this->MaintainVariable('AbsentSetTemp', 'Temperatur Abwesenheit', vtFloat, '~Temperature', 1, $create); 
			
			// Create Window Open Set Temperature
        		//$create = $this->ReadPropertyBoolean('CreateWindowSetTemp');
			$this->MaintainVariable('WindowSetTemp', 'Temperatur Offenes Fenster', vtFloat, '~Temperature', 2, $create);
		}

	}
