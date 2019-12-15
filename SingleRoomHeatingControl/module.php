<?php
	class SingleRoomHeatingControl extends IPSModule {

		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			// Temperature Parameter
			$this->RegisterPropertyInteger('Messenger_ID', 0);
			$this->RegisterPropertyInteger('Messenger_ID', 0);
			$this->RegisterPropertyString('IPAdress', "");
			$this->RegisterPropertyString('Name', "Hikvision Cam");
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
