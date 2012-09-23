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

$_url = array_merge($url, array('action' => str_replace(Configure::read('Routing.admin') . '_', '', $this->action)));
foreach (array('page', 'order', 'sort', 'direction') as $named) {
	if (isset($this->passedArgs[$named])) {
		$_url[$named] = $this->passedArgs[$named];
	}
}
if ($target) {
	$_url['action'] = str_replace(Configure::read('Routing.admin') . '_', '', 'comments');
	$ajaxUrl = $this->CommentWidget->prepareUrl(array_merge($_url, array('comment' => $comment, '#' => 'comment' . $comment)));
	echo $this->BootstrapForm->create(null, array('url' => $ajaxUrl, 'target' => $target));
} else {
	echo $this->BootstrapForm->create(null, array('url' => array_merge($_url, array('comment' => $comment, '#' => 'comment' . $comment)), 'class' => 'form form-vertical'));
}

echo $this->BootstrapForm->input('Comment.title', array(
	'label' => __d('comments', 'Comment Title'),
	'validate' => 'required:true',
	'class' => 'span6',
));

echo $this->BootstrapForm->input('Comment.body', array(
	'label' => __d('comments', 'Comment Body'),
	'validate' => 'required:true',
	'class' => 'span6',
	'error' => array(
		'body_required' => __d('comments', 'This field cannot be left blank'),
		'body_markup' => sprintf(__d('comments', 'You can use only headings from %s to %s'), 10, 20)
	),
));

// Bots will very likely fill this fields
echo $this->BootstrapForm->input('Other.title', array('type' => 'hidden'));
echo $this->BootstrapForm->input('Other.comment', array('type' => 'hidden'));
echo $this->BootstrapForm->input('Other.submit', array('type' => 'hidden'));

if ($target) {
	echo $this->Js->submit(__d('comments', 'Submit'), array_merge(array('url' => $ajaxUrl), $this->CommentWidget->globalParams['ajaxOptions']));
} else {
	echo $this->BootstrapForm->submit(__d('comments', 'Submit'), array('class' => 'btn btn-primary'));
}

echo $this->BootstrapForm->end();
