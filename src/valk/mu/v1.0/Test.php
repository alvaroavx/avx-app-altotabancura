<?php
class Test{
	private $Manifest;
	private $Gate;
	private $Data;
	public function __construct($Manifest, $Gate, $Data){
		$this->Manifest     = $Manifest;
		$this->Gate     = $Gate;
		$this->Data     = $Data;
	}
	public function Execute(){
		return [
			'Manifest' => $this->Manifest,
			'Gate' => $this->Gate,
			'Data' => $this->Data,
		];


	}
}