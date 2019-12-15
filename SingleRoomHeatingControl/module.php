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
			//$this->RegisterPropertyInteger('AbsenkTemp', 19);
			//$this->RegisterPropertyInteger('GrundTemp', 20);
			//$this->RegisterPropertyInteger('HeizTemp', 22);
			//$this->RegisterPropertyInteger('AntrAuf', 30);
			//$this->RegisterPropertyInteger('AntrZu', 6);
						
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
		}

	}
