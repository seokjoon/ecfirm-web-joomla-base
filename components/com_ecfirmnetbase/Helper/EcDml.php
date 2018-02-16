<?php
/**
 * @package ecfirm.net
 * @copyright Copyright (C) ecfirm.net. All rights reserved.
 * @license GNU General Public License version 2 or later.
 */

namespace Joomla\Component\EcfirmNetBase\Site\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/**
 * @deprecated
 */
class EcDml
{
	private static $db = null;

	public static function deleteByColumn($nameCol, $valueCol, $nameKey)
	{
		$db = self::getDbo(); //JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->delete('#__' . self::getPrefix() . '_' . $nameKey);
		$query->where($nameCol . ' = ' . $valueCol);
		$db->setQuery($query);

		return $db->execute();
	}

	private static function getDbo()
	{
		if(!(self::$db)) return Factory::getDbo();
		else return self::$db;
	}

	private static function getPrefix()
	{
		return substr(EcConst::getPrefix(), 0, 2);
	}

	public static function insertRecord($params, $nameKey)
	{
		$db = self::getDbo(); //JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$record = ArrayHelper::toObject($params);

		return $db->insertObject('#__' . self::getPrefix() . '_' . $nameKey, $record);
	}

	/**
	 * @see JTable은 model, table에서 사용가능
	 */
	public static function loadTable($valueKey, $nameKey, $nameCom)
	{
		$table = Table::getInstance($nameKey, ucfirst($nameCom) . 'Table');

		try {
			$table->load($valueKey);
			return $table;
		} catch (Exception $e) {
			EcDebug::lp($e->getMessage()); //$this->setError($e->getMessage());
			return FALSE;
		}
	}

	/**
	 * @XX
	 */
	public static function select($nameKey, $format = 'assocList')
	{
		$db = self::getDbo(); //JFactory::getDbo();
		$query = $db->getQuery(TRUE);
		$query->select('*')->from('#__' . self::getPrefix() . '_' . $nameKey);
		$db->setQuery($query);
		$out = 'load' . ucfirst($format);

		return $db->$out();
	}

	/**
	 * @param array $params: columns, format, limit, limitstart, order, table, where
	 * @param string $nameKey
	 * @return result, row, assoc, object, column, , rowList, assocList, objectList
	 */
	public static function selectByParams($params, $nameKey)
	{
		if (! isset($params['columns']) || empty($params['columns']))
			$params['columns'] = $nameKey;
		if (! isset($params['table']) || empty($params['table']))
			$params['table'] = '#__' . self::getPrefix() . '_' . $nameKey;
		else $params['table'] = '#__' . self::getPrefix() . '_' . $params['table'];
		$where = '';

		foreach ($params['where'] as $key => $value) {
			$where .= $key . '="' . $value . '"';
			unset($params['where'][$key]);
			if (count($params['where']) > 0) $where .= ' AND ';
		} //EcDebug::log('===='); EcDebug::log($where); EcDebug::log('====');

		$db = self::getDbo(); //JFactory::getDbo();
		$query = $db->getQuery(TRUE);

		$query->select($params['columns'])
			->from($params['table'])
			->where($where);
		if ((isset($params['order'])) && (! empty($params['order'])))
			$query->order($nameKey . ' ' . $params['order']);
		if ((isset($params['limit'])) && (isset($params['limitstart'])))
			$db->setQuery($query, $params['limitstart'], $params['limit']);
		else $db->setQuery($query);

		$params['format'] = ((isset($params['format']) && (! empty($params['format'])))) ? $params['format'] : 'result';
		$out = 'load' . ucfirst($params['format']); //EcDebug::log($out); //EcDebug::log($db->$out());

		return $db->$out();
	}

	public static function setDbo($db = null)
	{
		self::$db = $db;
	}

	/**
	 * @see JTable은 model, table에서 사용 가능
	 * @todo valueCol type handling
	 */
	public static function updateColumn($nameCom, $nameKey, $valuesKey, $nameCol, $valueCol)
	{ //EcDebug::lp($nameCom.':'.$nameKey.':'.$valuesKey.':'.$nameCol.':'.$valueCol); jexit();
		if (! (is_array($valuesKey))) $valuesKey = array( $valuesKey );

		$table = Table::getInstance($nameKey, ucfirst($nameCom) . 'Table');

		foreach ($valuesKey as $valueKey) {
			if (!($table->load($valueKey))) return false;
			$item[$nameKey] = $valueKey;
			$item[$nameCol] = $valueCol;
			$bool = $table->save($item);
			if (!($bool)) return $bool;
		}

		return true;
	}

	/**
	 * @see JTable은 model, table에서 사용 가능
	 */
	public static function updateColumnCount($nameCom, $nameKey, $valuesKey, $nameCol, $valueCol, $insert = false)
	{
		$bool = false;

		if (!(is_array($valuesKey))) $valuesKey = array( $valuesKey );

		$table = Table::getInstance($nameKey, ucfirst($nameCom) . 'Table');

		foreach ($valuesKey as $valueKey) {
			if (!($table->load($valueKey))) {
				if ($insert) {
					$bool = self::insertRecord(array( $nameKey => $valueKey ), $nameKey);
					if (!($bool)) break;
				} else return FALSE;
			}
			$item[$nameKey] = $valueKey;
			$item[$nameCol] = (int) $table->$nameCol + (int) $valueCol;
			$bool = $table->save($item);
			if (!$bool) return $bool;
		}

		return $bool;
	}
}