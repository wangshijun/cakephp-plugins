<?php
/**
 * 基于多租户架构的数据隔离, 对查询/删除/保存等操作增加额外的参数, 这些参数可动态开关
 *
 * Copyright (c) 2011 tomato
 *
 * Distributed under the terms of the MIT License.
 * Redistributions of files must retain the above copyright notice.
 *
 * PHP version 5
 *
 * @author	 tomato <wangshijun2010@gmail.com>
 * @copyright	(c) 2011 tomato <wangshijun2010@gmail.com>
 * @license	http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package	default
 * @subpackage	default
 */

App::uses('AuthComponent', 'Controller/Component');

class IsolatedBehavior extends ModelBehavior {

	protected $defaults = array(
		'isolator' => 'tenant_id',
		'delete' => true,
		'find' => true,
		'save' => true,
	);

	protected $isolator = null;

	/**
	 * 初始化选项, 并且视图从Session中获取数据隔离字段isolator的值
	 *
	 * @param object $Model 需要隔离数据的Model对象
	 * @param array $settings 配置选项
	 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaults;
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
		$this->isolator = $Model->user($this->settings[$Model->alias]['isolator']);
	}

	/**
	 * 设置是否在某些操作前执行数据隔离的操作,
	 *
	 * @param object $Model 需要隔离数据的Model对象
	 * @param mixed $methods 需要开关哪些回调/或者直接传入bool值配置所有
	 * @param boolean $enable 传入true打开回调, 传入false
	 * @access public
	 */
	public function configIsolated(Model $Model, $methods, $enable = true) {
		if (is_bool($methods)) {
			$enable = $methods;
			$methods = array('find', 'delete', 'save');
		}

		if (!is_array($methods)) {
			$methods = array($methods);
		}

		foreach($methods as $method) {
			$this->settings[$Model->alias][$method] = $enable;
		}
	}

	/**
	 * 每次删除之前看被删除的记录是否与当前登录用户在相同的租户中, 否则不删除
	 *
	 * @param object $Model 需要隔离数据的Model对象
	 * @param boolean $cascade 是否是级联删除
	 * @return boolean 如果当前数据与用户的数据隔离字段不同, 则不能删除
	 */
	public function beforeDelete(Model $Model, $cascade = true) {
		return true;
	}

	/**
	 * 每次查询之前设定隔离字段的值,
	 *
	 * @param object $Model 需要隔离数据的Model对象
	 * @param array $query 查询选项
	 * @return array $query 修改后的查询选项
	 * @todo php5.4 Illegal string offset warning
	 */
	public function beforeFind(Model $Model, $query) {
		$settings = $this->settings[$Model->alias];

		if ($this->isolator
			&& $settings['find']
			&& $Model->hasField($settings['isolator'])
			&& !isset($query['conditions'][$Model->alias . '.' . $settings['isolator']])
		) {
			@$query['conditions'][$Model->alias . '.' . $settings['isolator']] = $this->isolator;
		}

		return $query;
	}

	/**
	 * 每次保存之前设定隔离字段的值,
	 *
	 * @param object $Model 需要隔离数据的Model对象
	 * @param array $query 查询选项
	 * @return array $query 修改后的查询选项
	 */
	public function beforeSave(Model $Model) {
		$settings = $this->settings[$Model->alias];

		if ($this->isolator && $Model->hasField($settings['isolator'])
			&& !isset($Model->data[$Model->alias][$settings['isolator']])
		) {
			$Model->data[$Model->alias][$settings['isolator']] = $this->isolator;
		}

		return true;
	}

}
