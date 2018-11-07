<?php

include_once(__DIR__."/Mysql.php");


class CronjobHistory extends Mysql 
{
	public function addHistory() {
		$this->mysql->query("INSERT INTO diod.cronjobHistory SET created_at = NOW()");
		return $this->mysql->insert_id;
	}
}