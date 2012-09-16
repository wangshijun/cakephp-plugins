<?php
/**
 * Mark data records as deleted that can be recycled/deleted forever later
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

class RecyclableBehavior extends ModelBehavior {

	protected $defaults = array(
		'flag' => 'deleted',
		'date' => 'gmt_deleted',
		'delete' => true,
		'find' => true,
	);

	/**
	 * Initiate behaviour for the model using settings.
	 *
	 * @param object $Model Model using the behaviour
	 * @param array $settings Settings to override for model.
	 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaults;
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
	}

	/**
	 * Permanently deletes a record.
	 *
	 * @param object $Model Model from where the method is being executed.
	 * @param mixed $id ID of the soft-deleted record.
	 * @param boolean $cascade Also delete dependent records
	 * @return boolean Result of the operation.
	 * @access public
	 */
	public function drop(Model $Model, $id = null, $cascade = true) {
		$settings = $this->settings[$Model->alias];

		$this->configRecyclable($Model, false);

		$deleted = $Model->delete($id, $cascade);

		$this->configRecyclable($Model, 'delete', $settings['delete']);
		$this->configRecyclable($Model, 'find', $settings['find']);

		return $deleted;
	}

	/**
	 * Permanently deletes all records that were soft deleted.
	 *
	 * @param object $Model Model from where the method is being executed.
	 * @param boolean $cascade Also delete dependent records
	 * @return boolean Result of the operation.
	 * @access public
	 */
	public function purge(Model $Model, $cascade = true) {
		$purged = false;
		$settings = $this->settings[$Model->alias];

		if ($Model->hasField($settings['flag'])) {
			$this->configRecyclable($Model, false);

			$purged = $Model->deleteAll(array($settings['flag'] => '1'), $cascade);

			$this->configRecyclable($Model, 'delete', $settings['delete']);
			$this->configRecyclable($Model, 'find', $settings['find']);
		}

		return $purged;
	}

	/**
	 * Restores a soft deleted record, and optionally change other fields.
	 *
	 * @param object $Model Model from where the method is being executed.
	 * @param mixed $id ID of the soft-deleted record.
	 * @return boolean Result of the operation.
	 * @access public
	 */
	public function recycle(Model $Model, $id = null) {
		$settings = $this->settings[$Model->alias];
		if ($Model->hasField($settings['flag'])) {
			$id = empty($id) ? $Model->id : $id;

			$data = array($Model->alias => array(
				$Model->primaryKey => $id,
				$settings['flag'] => '0'
			));

			if (isset($settings['date']) && $Model->hasField($settings['date'])) {
				$data[$Model->alias][$settings['date']] = null;
			}

			$this->configRecyclable($Model, false);

			$Model->id = $id;
			$recycled = $Model->save($data, false);

			$this->configRecyclable($Model, 'find', $settings['find']);
			$this->configRecyclable($Model, 'delete', $settings['delete']);

			return ($recycled !== false);
		}

		return false;
	}

	/**
	 * Set if the beforeFind() or beforeDelete() should be overriden for specific model.
	 *
	 * @param object $Model Model about to be deleted.
	 * @param mixed $methods If string, method (find / delete) to enable on, if array array of method names, if boolean, enable it for find method
	 * @param boolean $enable If specified method should be overriden.
	 * @access public
	 */
	public function configRecyclable(Model $Model, $methods, $enable = true) {
		if (is_bool($methods)) {
			$enable = $methods;
			$methods = array('find', 'delete');
		}

		if (!is_array($methods)) {
			$methods = array($methods);
		}

		foreach($methods as $method) {
			$this->settings[$Model->alias][$method] = $enable;
		}
	}

	/**
	 * Run before a model is deleted, used to do a soft delete when needed.
	 *
	 * @param object $Model Model about to be deleted
	 * @param boolean $cascade If true records that depend on this record will also be deleted
	 * @return boolean Set to true to continue with delete, false otherwise
	 * @access public
	 */
	public function beforeDelete(Model $Model, $cascade = true) {
		$settings = $this->settings[$Model->alias];
		if ($settings['delete'] && $Model->hasField($settings['flag'])) {

			$data = array($Model->alias => array( $settings['flag'] => 1 ));

			if (isset($settings['date']) && $Model->hasField($settings['date'])) {
				$data[$Model->alias][$settings['date']] = date('Y-m-d H:i:s');
			}

			$deleted = $Model->save($data, false);

			if ($deleted && $cascade) {
				$Model->deleteDependent($Model->id, $cascade);
				$Model->deleteLinks($Model->id);
			}

			CakeSession::write($Model->alias . '.deleted', !empty($deleted));

			return false;
		}

		return true;
	}

	/**
	 * Run before a model is about to be find, used only fetch for non-deleted records.
	 *
	 * @param object $Model Model about to be deleted.
	 * @param array $query Data used to execute this query, i.e. conditions, order, etc.
	 * @return mixed Set to false to abort find operation, or return an array with data used to execute query
	 * @todo php 5.4 Warning (2): Illegal string offset 'Group.deleted'
	 */
	public function beforeFind(Model $Model, $query) {
		$settings = $this->settings[$Model->alias];

		if ($settings['find']
			&& $Model->hasField($settings['flag'])
			&& !isset($query['conditions'][$Model->alias . '.' . $settings['flag']])
		) {
			@$query['conditions'][$Model->alias . '.' . $settings['flag']] = 0;
		}

		return $query;
	}

}
