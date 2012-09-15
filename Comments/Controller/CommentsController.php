<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('CommentsAppController', 'Comments.Controller');
/**
 * Comments Controller
 *
 * @package comments
 * @subpackage comments.controllers
 */

/**
 * @property Comment Comment
 * @property PrgComponent Prg
 * @property SessionComponent  Session
 * @property RequestHandlerComponent RequestHandler
 */
class CommentsController extends CommentsAppController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Comments';

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'RequestHandler',
		'Paginator',
		'Session');

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Text', 'Time');

/**
 * Uses
 *
 * @var array
 */
	public $uses = array('Comments.Comment');

	// Search.Searchable Settings
	public $presetVars = array(
		array('field' => 'title', 'type' => 'value'),
		array('field' => 'tenant_id', 'type' => 'value'),
	);

	public $paginate = array(
		'conditions' => array(),
		'contain' => array('UserModel'),
		'order' => array('Comment.created' => 'DESC'),
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Comment->recursive = 0;
		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'UserModel'  => array(
					'className' => 'User',
					'foreignKey' => 'user_id',
				)
			)
		), false);
	}

/**
 * Admin index action
 *
 * @param string
 * @return void
 */
	public function admin_index($type = '') {
		if ($type == 'spam') {
			$this->paginate['conditions']['Comment.is_spam']  = array('spam', 'spammanual');
		} elseif ($type == 'clean') {
			$this->paginate['conditions']['Comment.is_spam'] = array('ham', 'clean');
		}

		$this->set('comments', $this->paginate());
	}

/**
 * Processes mailbox folders
 *
 * @param string $folder Name of the folder to process
 * @return void
 */
	public function admin_process($type = null) {
		$addInfo = '';
		if (!empty($this->data)) {
			try {
				$message = $this->Comment->process($this->data['Comment']['action'], $this->data);
			} catch (Exception $ex) {
				$message = $ex->getMessage();
			}
			$this->Session->setFlash($message, 'success');
		}
		$url = array('plugin'=>'comments', 'action' => 'index', 'admin' => true);
		$url = Set::merge($url, $this->params['pass']);
		$this->redirect(Set::merge($url, $this->params['named']));
	}

/**
 * Admin mark comment as spam
 *
 * @param string UUID
 */
	public function admin_spam($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists(true)) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id', true), 'error');
		} elseif ($this->Comment->markAsSpam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about spam message.', true), 'success');
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.', true), 'error');
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin mark comment as ham
 *
 * @param string UUID
 */
	public function admin_ham($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists(true)) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id',true), 'error');
		} elseif ($this->Comment->markAsHam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about ham message.', true), 'success');
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.', true), 'error');
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin View action
 *
 * @param string UUID
 */
	public function admin_view($id = null) {
		$this->Comment->id = $id;
		$comment = $this->Comment->read(null, $id);
		if (empty($comment)) {
			$this->Session->setFlash(__d('comments', 'Invalid Comment.', true), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Admin delete action
 *
 * @param string UUID
 */
	public function admin_delete($id = null) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists()) {
			$this->Session->setFlash(__d('comments', 'Invalid id for Comment', true), 'error');
		} elseif ($this->Comment->delete()) {
			$this->Session->setFlash(__d('comments', 'Comment deleted', true), 'success');
		} else {
			$this->Session->setFlash(__d('comments', 'Impossible to delete the Comment. Please try again.', true), 'error');
		}
		$this->redirect(array('action'=>'index'));
	}

/**
 * View action
 *
 * @param string UUID
 */
	public function view($id = null) {
		$this->Comment->id = $id;
		$comment = $this->Comment->read(null, $id);
		if (empty($comment)) {
			$this->Session->setFlash(__d('comments', 'Invalid Comment.', true), 'error');
			return $this->redirect(array('action'=>'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Request comments
 *
 * @param string user UUID
 * @return void
 */
	public function requestForUser($userId = null, $amount = 5) {
		if (!$this->RequestHandler->isAjax() && !$this->_isRequestedAction()) {
			return $this->cakeError('404');
		}

		$conditions = array('Comment.user_id' => $userId);
		if (!empty($this->params['named']['model'])) {
			$conditions['Comment.model'] = $this->params['named']['model'];
		}
		$conditions['Comment.is_spam'] = array('ham','clean');
		$this->paginate = array(
			'conditions' => $conditions,
			'order' => 'Comment.created DESC',
			'limit' => $amount);

		$this->set('comments', $this->paginate());
		$this->set('userId', $userId);

		$this->viewPath = 'elements/comments';
		$this->render('comment');
	}

/**
 * Returns true if the action was called with requestAction()
 *
 * @return boolean
 */
	protected function _isRequestedAction() {
		return array_key_exists('requested', $this->params);
	}
}
