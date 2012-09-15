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

if ($this->CommentWidget->globalParams['target']) {
	$this->Paginator->options(array_merge(
		array('url' => $this->CommentWidget->prepareUrl($url)),
		$this->CommentWidget->globalParams['ajaxOptions']));
} else {
	$this->Paginator->options(array('url' => $url));
}

?>

<?php if (!empty(${$viewComments})): ?>
	<?php echo $this->element('paginator'); ?>
<?php endif; ?>
