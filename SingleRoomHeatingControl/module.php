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
			
			// Variablen
			$this->RegisterPropertyBoolean('HeizAuto', true);
			$this->RegisterPropertyInteger('HeizProg', 1);
			$this->RegisterPropertyFloat('HeizTemp', 22.0);
						
			// Time Schedule
			$this->RegisterPropertyInteger('WeeklyTimeTableEventID', 0);
			$this->RegisterPropertyInteger('HolidayIndicatorID', 0);
			$this->RegisterPropertyInteger('DayUsedWhenHoliday',6);
			
			// Contacts
			$this->RegisterPropertyBoolean('WindowID', 0);
			
			// Presence
			$this->RegisterPropertyBoolean('PresenceID', 0);
			
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
			$HeizAuto = $this->ReadPropertyBoolean('HeizAuto');
			 
			 // Heizungsprogramm 
			$HeizProg = $this->ReadPropertyInteger('HeizProg');
			 
			// Fensterkontakt 
			$win = $this->ReadPropertyBoolean('WindowID');
			 
			 // Anwesenheit 
			$pres = $this->ReadPropertyBoolean('PresenceID');
			 
			 // Absenktemperatur
			$AbsenkTemp = $this->ReadPropertyFloat('AbsenkTemp');
			 
			 // Heiztemperatur
			$HeizTemp = $this->ReadPropertyFloat('HeizTemp');
			 
			 // Homematic Instance
			$HM_InstanzID = $this->ReadPropertyInteger('HM_InstanzID');
			 
			 // Steuerung 
			If ($HeizProg == 1) //IPS Betrieb
			{
				//Abwesend
				If ($win == true) 
				{
					If ($HeizAuto == true) //Hier muss die Temperatur < 16°C sein
					{
						HM_WriteValueFloat($HM_InstanzID, 'MANU_MODE',$AbsenkTemp);
					}
				}
				//Anwesend
				Else If ($win == false) 
				{
					HM_WriteValueFloat($HM_InstanzID, 'MANU_MODE',$HeizTemp);
				}
			}
			
		}

	}
