<?php

class ConfigException extends RuntimeException {}

class Config
{
	private static $files = array();
	private static $items = array();
	private static $paths = array('.');
	private static $ext = '.php';

	/**
	 * 設定ファイルを読み込んで保持配列にセット 
	 *
	 * <code>
	 * <?php
	 * // read user.php
	 * require_once('config.php');
	 * Config::load('user');
	 * ?>
	 * </code
	 *
	 * @param	string		$file		ファイル名(拡張子なし)	
	 * @return	boolean
	 */
	public static function load($file)
	{
		if (array_key_exists($file, static::$files)) {
			return;
		}

		$has_file = false;
		foreach (static::$paths as $path) {
			$file_name = sprintf('%s/%s%s', $path, $file, static::$ext);
			if (file_exists($file_name)) {
				$has_file = true;
				break;
			}
		}

		if (!$has_file) {
			throw new ConfigException('指定されたファイルが存在しません');
		}

		$original_config = require($file_name);
		foreach ($original_config as $key => $value) {
			static::setArr(static::$items[$file], $key, $value);
		}

		static::$files[$file] = true;
		return true;
	}

	/**
	 * 指定されたキーの設定値を取得する 
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
	 * @param	string		$key		取得したい設定値のキー名	
	 * @param	mixed		$default	デフォルト値	
	 * @return	mixed
	 */
	public static function get($key, $default = null)
	{
		$key_part = explode(".", $key);
		if (is_null($key_part) or 1 == count($key_part)) {
			throw new ConfigException('キー名の指定が不正です');
		}

		$file = array_shift($key_part);	
		static::load($file);

		$config = static::$items[$file];
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
	 * 設定ファイルの検索パスを設定 
	 *
	 * <code>
	 * <?php
	 * require_once('Config.php');
	 * Config::setPath('app/config');
	 * Config::setPath(array('app/config', 'sys/config');
	 * ?>
	 * </code>
	 *
	 * @param	mixed		$paths	ディレクトリパス	
	 * @return	void
	 */
	public static function setPath($paths)
	{
		if (is_array($paths)) {
			foreach ($paths as $path) {
				static::$paths[] = $path;
			}
		} else {
			static::$paths[] = $paths;
		}
	}

	/**
	 * 保持配列に設定をセットする 
	 *
	 * @param	array		$arr		設定を保持する配列	
	 * @param	string		$key		設定キー名	
	 * @param	mixed		$value		設定値	
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
