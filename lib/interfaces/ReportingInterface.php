<?php

interface ReportingInterface 
{
	public function __construct($checker, array $settings, $debug = 0);
	public function _checkSettings();
	public function _read();
}