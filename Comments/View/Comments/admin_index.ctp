<div class="comments index">
	<h2><?php echo __d('comments', 'Comments');?></h2>

	<?php echo $this->BootstrapForm->create('Comment', array('url' => array_merge(array('action' => 'search'), $this->params['pass']), 'class' => 'search form-search form-inline'));?>
		<?php echo $this->BootstrapForm->input('title', array('class' => 'short', 'placeholder' => __d('comments', 'type to search'), 'class' => 'input-medium search-query', 'label' => false, 'div' => false)); ?>
		<?php if (is_root_tenant()): ?>
			<?php echo $this->BootstrapForm->input('tenant_id', array('validate' => 'min:1', 'label' => false, 'div' => false, 'class' => 'input-medium')); ?>
		<?php endif; ?>
		<?php echo $this->BootstrapForm->button('<i class="icon-search"></i> ' . __d('comments', 'Search'), array('class' => 'btn', 'div' => false));?>
		<?php echo $this->Html->link('<i class="icon-trash"></i> ' . __d('comments', 'Spam Comments'), array('action' => 'index', 'spam'), array('class' => 'btn', 'escape' => false));?>
	<?php echo $this->BootstrapForm->end();?>

	<?php if (!empty($comments)): ?>

	<?php echo $this->BootstrapForm->create('Comment',array(
		'id' => 'CommentForm',
		'name' => 'CommentForm',
		'class' => 'search form-search form-inline',
		'url' => Set::merge(array('action' => 'process'), $this->params['named'])
	));?>

	<table cellpadding="0" cellspacing="0" class="comments table table-bordered table-striped">
		<thead>
			<tr>
				<th><input id="mainCheck" style="width: 100%;" type="checkbox" onclick="$('.cbox').each (function (id,f) {$('#'+this.id).attr('checked', !!$('#mainCheck').attr('checked'))})"></th>
				<th><?php echo $this->Paginator->sort('author_name');?></th>
				<th width="10%"><?php echo $this->Paginator->sort('title');?></th>
				<th width="40%"><?php echo $this->Paginator->sort('body');?></th>
				<th><?php echo $this->Paginator->sort('approved');?></th>
				<th><?php echo $this->Paginator->sort('created');?></th>
				<th><?php echo $this->Paginator->sort('modified');?></th>
				<th class="actions"><?php echo __d('comments', 'Actions');?></th>
			</tr>
		</thead>
		<tbody>
		<?php $i = 0; foreach ($comments as $comment): ?>
			<tr>
				<td class="comment-check">
					<?php echo $this->Form->input('Comment.' . $comment['Comment']['id'], array(
						'label' => false,
						'div' => false,
						'class' => 'cbox',
						'type' => 'checkbox')); ?>
				</td>
				<td><?php echo $comment['UserModel']['name'] ? h($comment['UserModel']['name']) : h($comment['Comment']['author_name']); ?>&nbsp;</td>
				<td><?php echo h($comment['Comment']['title']); ?>&nbsp;</td>
				<td rel="tooltip" title="<?php echo h($comment['Comment']['body']);?>"><?php echo mb_substr(h($comment['Comment']['body']), 0, 50); ?>&nbsp;</td>
				<td class="toggle" title="<?php echo __('Click to toggle'); ?>">
					<?php if ($comment['Comment']['approved']): ?>
						<span class="badge badge-success">
							<?php echo $this->Html->link(__('Yes'), array('action' => 'toggle', $comment['Comment']['id'], 'approved'));?>
						</span>
					<?php else: ?>
						<span class="badge badge-error">
							<?php echo $this->Html->link(__('No'), array('action' => 'toggle', $comment['Comment']['id'], 'approved'));?>
						</span>
					<?php endif; ?>
				</td>
				<td><?php echo h($comment['Comment']['created']); ?>&nbsp;</td>
				<td><?php echo h($comment['Comment']['modified']); ?>&nbsp;</td>
				<td class="actions">
					<?php echo $this->Html->link('<i class="icon-eye-open"></i> ' . __d('comments', 'Reply'), array('action' => 'edit', $comment['Comment']['id']), array('escape' => false, 'class' => 'btn btn-mini')); ?>
					<?php echo $this->Html->link('<i class="icon-eye-open"></i> ' . __d('comments', 'Delete'), array('action' => 'delete', $comment['Comment']['id']), array('escape' => false, 'class' => 'btn btn-mini'), sprintf(__d('comments', 'Are you sure you want to delete # %s?'), $comment['Comment']['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php echo $this->BootstrapForm->input('Comment.action', array(
		'label' => false,
		'div' => false,
		'type' => 'select',
		'options' => array(
			'delete' => __d('comments', 'Delete'),
			'approve' => __d('comments', 'Approve'),
			'disapprove' => __d('comments', 'Dispprove'),
		)));?>
	<?php echo $this->BootstrapForm->submit(__d('comments', 'Process'), array('name' => 'process', 'class' => 'btn', 'div' => false));?>

	<?php echo $this->element('paginator'); ?>

	<?php echo $this->BootstrapForm->end(); ?>

	<?php else: ?>

	<div class="alert alert-info">
		<a class="close" data-dismiss="alert">×</a>
		<strong><?php echo __d('comments', 'Oops');?></strong> <?php echo __d('comments', 'No data found');?>
	</div>

	<?php endif; ?>

</div>

<?php echo $this->start('sidebar'); ?>
<?php echo $this->element('menu'); ?>
<?php echo $this->end(); ?>