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
?>
<div class="comments">

	<!-- 评论摘要 -->
	<?php if (!($isAddMode && empty(${$viewComments}))): ?>
	<div id="comment-summary">
		<?php if ($allowAddByAuth): ?>
			<?php if (!$isAddMode): ?>
				<?php if (empty($this->params[$adminRoute]) && $allowAddByAuth): ?>
					<?php echo $this->CommentWidget->link( __d('comments', 'Add Comment'), array_merge($url, array('comment' => 0)), array('class' => 'btn btn-primary pull-right', 'escape' => false)); ?>
				<?php endif; ?>
			<?php endif; ?>
			<h3><?php echo __d('comments', 'Comments'); ?></h3>
		<?php else: ?>
			<small class="pull-right">
				<?php echo sprintf(__d('comments', 'If you want to post comments, you need to login first.')); ?>
				<?php echo $this->Html->link(__d('comments', 'login'), array('controller' => 'users', 'action' => 'login', 'prefix' => $adminRoute, $adminRoute => false)); ?>
			</small>
			<h3><?php echo __d('comments', 'Comments'); ?></h3>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<!-- 评论内容 -->
	<div id="comment-content">
		<?php foreach (${$viewComments} as $comment): ?>
			<?php echo $this->CommentWidget->element('item', array('comment' => $comment)); ?>
		<?php endforeach; ?>
		<?php echo $this->CommentWidget->element('paginator'); ?>
	</div>

	<!-- 评论表单 -->
	<?php if ($isAddMode && $allowAddByAuth): ?>
		<div id="comment-form">
			<h3><?php echo __d('comments', 'Add New Comment'); ?></h3>
			<?php echo $this->CommentWidget->element('form', array('comment' => (!empty($comment) ? $comment : 0))); ?>
		</div>
	<?php endif; ?>

</div>
<?php echo $this->Html->image('/comments/img/indicator.gif', array('id' => 'busy-indicator',  'style' => 'display:none;')); ?>
