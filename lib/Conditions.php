<?php

/** 
* 
* @class Conditions
* 
*/
class Conditions extends classError {
		private $_checker;

		public function __construct($checker, $debug = 0) {
			$this->setDebug($debug);
			$this->_checker = $checker;
		}

		/**
		* @function compareWithPrevious
		* @param number number required
		* @param key enum required
		* @param type enum('percent','value') required
		* @param condition enum('gt','lt','lte','gte') required
		* 
		*/			
		public function _compareWithPrevious($value, $key, $kind,$condition, $groupKey, $index, $data)
		{
			$checkerData = $this->_checker->readData();
			
			if($index == 0) {
				return false;
			} else {
				$previous = $checkerData[$groupKey][$index - 1];
				$previousVal = $previous[$key];

				$current = $data;
				$currentVal = $current[$key];

				switch($kind){
					case 'percent': 
						$percent = abs(100-(($previousVal/$currentVal)*100));
						return call_user_func(array($this,$condition), $value,'v', $groupKey, $index, ['v'=>$percent]);
					break;
					case 'value':
						// if(call_user_func($this->$condition, $value,'v',['v'=>$percent]) == true){
						// 	return false;
						// } TODO TODO TODO TODO
					break;
				}
			}
		}

		/**
		* @function compareWithTime
		* @param number number required
		* @param key enum required
		* @param condition enum('gt','lt','lte','gte') required
		* 
		*/
		public function _compareWithTime($value, $key, $condition, $groupKey, $index, $data){
			$date = time() + $value;
			return call_user_func(array($this,$condition), $date,'v', $groupKey, $index, ['v'=>$data[$key]]);
		}

		/**
		* @function eqWithKeys
		* @param key enum required
		* @param key1 enum required
		* 
		*/
		public function _eqWithKeys($key,$key1, $groupKey, $index, $data){
			return $this->eq($data[$key], $key1, $groupKey, $index, $data);
		}

		/**
		* @function gtWithKeys
		* @param key enum required
		* @param key1 enum required
		* 
		*/
		public function _gtWithKeys($key,$key1, $groupKey, $index, $data){
			return $this->gt($data[$key], $key1, $groupKey, $index, $data);
		}

		/**
		* @function ltWithKeys
		* @param key enum required
		* @param key1 enum required
		* 
		*/
		public function _ltWithKeys($key,$key1, $groupKey, $index, $data){
			return $this->lt($data[$key], $key1, $groupKey, $index, $data);
		}

		/**
		* @function lteWithKeys
		* @param key enum required
		* @param key1 enum required
		* 
		*/
		public function _lteWithKeys($key,$key1, $groupKey, $index, $data){
			return $this->lte($data[$key], $key1, $groupKey, $index, $data);
		}

		/**
		* @function gteWithKeys
		* @param key enum required
		* @param key1 enum required
		* 
		*/
		public function _gteWithKeys($key,$key1, $groupKey, $index, $data){
			return $this->gte($data[$key], $key1, $groupKey, $index, $data);
		}

		/**
		* @function eq
		* @param value number required
		* @param key enum required
		*/			
		public function _eq($value,$key, $groupKey, $index, $data)
		{
			return $data[$key] == $value;
		}

		/**
		* @function gt
		* @param value number required
		* @param key enum required
		*/			
		public function _gt($value,$key, $groupKey, $index, $data)
		{
			return $data[$key] > $value;
		}

		/**
		* @function gte
		* @param value number required
		* @param key enum required
		*/			
		public function _gte($value,$key, $groupKey, $index, $data)
		{
			return $data[$key] >= $value;
		}

		/**
		* @function lt
		* @param value number required
		* @param key enum required
		*/			
		public function _lt($value,$key, $groupKey, $index, $data)
		{
			return $data[$key] < $value;
		}
		
		/**
		* @function lte
		* @param value number required
		* @param key enum required
		*/			
		public function _lte($value,$key, $groupKey, $index, $data)
		{	
			return $data[$key] <= $value;
		}
		
		/**
		* @function between
		* @param value number required
		* @param value2 number required
		* @param key enum required
		*/			
		public function _between($value,$value2,$key, $groupKey, $index, $data)
		{
			return $this->gt($value,$key, $groupKey, $index, $data) && $this->lt($value2,$key, $groupKey, $index, $data);
		}
		
		/**
		* @function betweenEq
		* @param value number required
		* @param value2 number required
		* @param key enum required
		*/			
		public function _betweenEq($value,$value2,$key, $groupKey, $index, $data)
		{
			return $this->gte($value,$key, $groupKey, $index, $data) && $this->lte($value2,$key, $groupKey, $index, $data);
		}

		/**
		* @function cronDailyRun
		* @param date date required
		* @param key enum required
		* @param condition enum('eq') required
		*/
		public function _cronDailyRun($value,$key, $conditionFunc, $groupKey, $index, $data){
			$cronLastTime = date('Y-m-d', $data[$key]);
			$arr = [];
			$arr['lastTimeDate'] = $cronLastTime;
			return $this->$conditionFunc($value, 'lastTimeDate', $groupKey, $index, $arr);
		} 		

	}