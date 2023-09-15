<?php
/*
 * @Author: zeyudada
 * @Date: 2022-04-12 18:51:32
 * @LastEditTime: 2022-05-09 19:58:43
 * @Description: mysqli操作类库
 * @Q Q: zeyunb@vip.qq.com(1776299529)
 * @E-mail: admin@zeyudada.cn
 * 
 * Copyright (c) 2022 by zeyudada, All Rights Reserved. 
 */
class DB
{
	var $link = null;

	function __construct($db_host, $db_user, $db_pass, $db_name, $db_port = 3306)
	{

		$this->link = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

		if (!$this->link) die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());

		//mysqli_select_db($this->link, $db_name) or die(mysqli_error($this->link));


		$this->query("set sql_mode = ''");
		//字符转换，读库
		$this->query("set character set 'utf8'");
		//写库
		$this->query("set names 'utf8'");

		return true;
	}
	function fetch($q)
	{
		return mysqli_fetch_assoc($q);
	}
	function get_row($q)
	{
		$result = $this->query($q);
		return mysqli_fetch_assoc($result);
	}
	//查询全部数据
	public function getAll($sql)
	{
		$res = $this->query($sql);
		while ($row =  mysqli_fetch_assoc($res)) {
			$data[] = $row;
		}
		return $data;
	}
	function count($q)
	{
		$result = $this->query($q);
		$count = mysqli_fetch_array($result);
		return $count[0];
	}
	function query($q)
	{
		$result = mysqli_query($this->link, $q);
		if (!$result) {
			global $telegram;
			$telegram->finderror($this->error());
			die('Query Error ' . $this->error());
		}
		return $result;
	}
	function escape($str)
	{
		return mysqli_real_escape_string($this->link, $str);
	}
	function insert($q)
	{
		if ($this->query($q))
			return mysqli_insert_id($this->link);
		return false;
	}
	function affected()
	{
		return mysqli_affected_rows($this->link);
	}
	function insert_array($table, $array)
	{
		$q = "INSERT INTO `$table`";
		$q .= " (`" . implode("`,`", array_keys($array)) . "`) ";
		$q .= " VALUES ('" . implode("','", array_values($array)) . "') ";

		if ($this->query($q)) return mysqli_insert_id($this->link);
		return false;
	}
	function error()
	{
		$error = mysqli_error($this->link);
		$errno = mysqli_errno($this->link);
		return '[' . $errno . '] ' . $error;
	}
	function close()
	{
		$q = mysqli_close($this->link);
		return $q;
	}
}
