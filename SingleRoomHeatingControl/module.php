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
			$this->RegisterPropertyInteger('HM_InstanzID',null);
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
			
			// Create Heizautomatik
			$this->MaintainVariable('HeizAuto', 'Heizautomatik', vtBoolean, 'Switch', 1, true);
			
			// Create Heizprogramm
        		$this->MaintainVariable('HeizProg', 'Heizprogramm', vtInteger, 'Heizungsautomatik', 2, true);
							
			// Create Heiztemperatur
        		$this->MaintainVariable('HeizTemp', 'Heiztemperatur', vtFloat, '~Temperature', 3, true);
			
		}
		
		 public function Update()
    		{
        		$result = 'Ergebnis konnte nicht ermittelt werden!';
        		// Daten lesen
       			 $state = true;
			
			// Heizungsautomatik 
			$HeizAuto = $this->ReadPropertyInteger('HeizAuto');
        		if ($HeizAuto != 0) {
            			$HeizAuto = GetValue($HeizAuto);
        		} else {
            			$this->SendDebug('UPDATE', 'HeizAuto not set!');
            			$state = false;
        			}
			 
			 // Heizungsprogramm 
			$HeizProg = $this->ReadPropertyInteger('HeizProg');
        		if ($HeizProg != 0) {
            			$HeizProg = GetValue($HeizProg);
        		} else {
            			$this->SendDebug('UPDATE', 'HeizProg not set!');
            			$state = false;
        			}
			 
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
			 
			 // Absenktemperatur
			$AbsenkTemp = $this->ReadPropertyInteger('AbsenkTemp');
        		if ($AbsenkTemp != 0) {
            			$win = GetValue($AbsenkTemp);
        		} else {
            			$this->SendDebug('UPDATE', 'AbsenkTemp not set!');
            			$state = false;
        			}
			 
			 // Absenktemperatur
			$HeizTemp = $this->ReadPropertyInteger('HeizTemp');
        		if ($HeizTemp != 0) {
            			$win = GetValue($HeizTemp);
        		} else {
            			$this->SendDebug('UPDATE', 'HeizTemp not set!');
            			$state = false;
        			}
			 
			 // Steuerung 
			If ($HeizProg == 1) //IPS Betrieb
			{
				//Abwesend
				If ($pres == false) 
				{
					If ($HeizAuto == true) //Hier muss die Temperatur < 16°C sein
					{
						HM_WriteValueFloat($HM_InstanzID, 'MANU_MODE',$AbsenkTemp);
					}
				}
				//Anwesend
				Else If ($pres == true) 
				{
					HM_WriteValueFloat($HM_InstanzID, 'MANU_MODE',$HeizTemp);
				}
			}
			
		}

	}
