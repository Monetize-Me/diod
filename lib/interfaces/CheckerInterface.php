<?php

interface CheckerInterface{
	public function __construct(array $settings, $debug = 0);
	public function _checkSettings();
	public function _run();
	public function _readData();
}


