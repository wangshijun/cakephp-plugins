<?php
/**
 * Attachments Controller
 *
 * PHP version 5
 *
 * @author	 tomato <wangshijun2010@gmail.com>
 * @copyright	(c) 2011 tomato <wangshijun2010@gmail.com>
 * @package	Misc
 * @subpackage	Attachments
 */
App::uses('Media/MediaAppController', 'Controller');

class AttachmentsController extends MediaAppController {

	protected $redirect = array('controller' => 'pages', 'action' => 'display', 'home');

	protected $mine_types = array(
		'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
	);

	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('download'));
	}

	/**
	 * 下载附件, 使用MediaView
	 *
	 * @param int $attachment_id 附件ID
	 * @return void
	 */
	 public function download($attachment_id = null) {

		$attachment = $this->Attachment->read(null, $attachment_id);
		 if (!$attachment) {
			throw new NotFoundException(__('Invalid attachment'));
		 }

		extract(pathinfo($attachment['Attachment']['basename']));

		$mine_type = array_key_exists($extension, $this->mine_types)
			? array($extension => $this->mine_types[$extension])
			: array();

		$params = array(
			'id' => $attachment['Attachment']['basename'],
			'name' => $filename,
			'download'  => true,
			'extension' => $extension,
			'mimeType' => $mine_type,
			'path' => MEDIA_TRANSFER . $attachment['Attachment']['dirname'] . DS
		);

		//debug($params); exit();

		$this->viewClass = 'Media';
		$this->set($params);
	}

}
