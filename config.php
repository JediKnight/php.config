<?php

class ConfigException extends RuntimeException {}

class Config
{
	private static $_files = array();
	private static $_items = array();
	private static $_paths = array('.');
	private static $_ext = '.php';

	/**
	 * Reads the configuration file
	 *
	 * <code>
	 * <?php
	 * // read user.php
	 * Config::load('user');
	 * ?>
	 * </code
	 *
	 * @param	string		$file		File name you want to import	
	 * @return	boolean
	 */
	public static function load($file)
	{
		if (array_key_exists($file, static::$_files)) {
			return;
		}

		$has_file = false;
		foreach (static::$_paths as $path) {
			$file_name = sprintf('%s/%s%s', $path, $file, static::$_ext);
			if (file_exists($file_name)) {
				$has_file = true;
				break;
			}
		}

		if (!$has_file) {
			throw new ConfigException('The specified file does not exist.');
		}

		$original_config = require($file_name);
		foreach ($original_config as $key => $value) {
			static::setArr(static::$_items[$file], $key, $value);
		}

		static::$_files[$file] = true;
		return true;
	}

	/**
	 * returns a config setting
	 *
	 * <code>
	 * // user.php
	 * <?php
	 * return array(
	 * 		'name' => 'Joe',
	 * 		'email' => array(
	 * 			'company' => 'xx@xx.xx',
	 * 			'private' => 'yy@yy.yy'
	 * 		)
	 * 	);
	 * ?>
	 * 
	 * <?php
	 * require_once('Config.php');
	 * $name = Config::get('user.name');			// Joe
	 * $sex = Config::get('user.sex', 'male');		// male
	 * $email = Config::get('user.email.company');	// xx@xx.xx
	 * ?>
	 * </code>
	 *
	 * @param	string		$key		Configuration key name you want to get	
	 * @param	mixed		$default	Set value when there is no value
	 * @return	mixed
	 */
	public static function get($key, $default = null)
	{
		$key_part = explode(".", $key);
		if (is_null($key_part) or 1 == count($key_part)) {
			throw new ConfigException('You incorrectly specified the key.');
		}

		$file = array_shift($key_part);	
		static::load($file);

		$config = static::$_items[$file];
		foreach ($key_part as $part) {
			if (isset($config[$part])) {
				$config = $config[$part];
			} else {
				$config = $default;
				break;
			}
		}

		return $config;
	}

	/**
	 * Name of the directory in which the configuration file is put
	 *
	 * <code>
	 * <?php
	 * require_once('Config.php');
	 * Config::setPath('app/config');
	 * Config::setPath(array('app/config', 'sys/config');
	 * ?>
	 * </code>
	 *
	 * @param	mixed		$paths	Name of the directory you want to set
	 * @return	void
	 */
	public static function setPath($paths)
	{
		if (is_array($paths)) {
			foreach ($paths as $path) {
				static::$_paths[] = $path;
			}
		} else {
			static::$_paths[] = $paths;
		}
	}

	/**
	 * Set the sequence read from the configuration file to set retention sequence 
	 *
	 * @param	array		$arr		Retention sequence set
	 * @param	string		$key		Key name you want to set
	 * @param	mixed		$value		I want to set the value
	 * @return	void
	 */
	private static function setArr(&$arr, $key, $value)
	{
		if (is_array($value)) {
			foreach ($value as $sub_key => $sub_value) {
				static::setArr($arr[$key], $sub_key, $sub_value);
			}
		} else {
			$arr[$key] = $value;
		}
	}
}
