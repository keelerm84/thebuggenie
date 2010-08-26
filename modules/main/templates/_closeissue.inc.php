<div class="rounded_box white borderless shadowed backdrop_box small" style="padding: 5px; text-align: left; font-size: 13px;">
	<div class="backdrop_detail_header"><?php echo __('Close this issue'); ?></div>
	<form action="<?php echo make_url('closeissue', array('project_key' => $issue->getProject()->getKey(), 'issue_id' => $issue->getID())); ?>" method="post" accept-charset="<?php echo TBGContext::getI18n()->getCharset(); ?>">
		<div class="backdrop_detail_content">
			<?php echo __('Do you want to change some of these values as well?'); ?>
			<input type="hidden" name="issue_action" value="close">
			<ul>
				<li>
					<input type="checkbox" name="set_status" id="close_issue_set_status" value="1"><label for="close_issue_set_status"><?php echo __('Status'); ?></label>
					<select name="status_id">
						<option value="0"> </option>
						<?php foreach ($statuses as $status): ?>
							<option value="<?php echo $status->getID(); ?>"><?php echo $status->getName(); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<li id="close_issue_resolution_div"<?php if (!$issue->isResolutionVisible()): ?> style="display: none;"<?php endif; ?>>
					<input type="checkbox" name="set_resolution" id="close_issue_set_resolution" value="1"><label for="close_issue_set_resolution"><?php echo __('Resolution'); ?></label>
					<select name="resolution_id">
						<option value="0"> </option>
						<?php foreach ($fields_list['resolution']['choices'] as $resolution): ?>
							<option value="<?php echo $resolution->getID(); ?>"><?php echo $resolution->getName(); ?></option>
						<?php endforeach; ?>
					</select>
				</li>
				<?php if (!$issue->isResolutionVisible()): ?>
					<li id="close_issue_resolution_link" class="faded_medium">
						<?php echo __("Resolution isn't visible for this issuetype / product combination"); ?>
						<a href="javascript:void(0);" onclick="$('close_issue_resolution_link').hide();$('close_issue_resolution_div').show();"><?php echo __('Set anyway'); ?></a>
					</li>
				<?php endif; ?>
				<li>
					<label for="close_comment"><?php echo __('Write a comment if you want it to be added'); ?></label>
					<textarea name="close_comment" id="close_comment" style="width: 372px; height: 50px;"></textarea>
				</li>
			</ul>
			<div style="text-align: right; margin-right: 5px;">
				<input type="submit" value="<?php echo __('Close issue'); ?>">
			</div>
		</div>
		<div class="backdrop_detail_footer">
			<?php echo '<a href="javascript:void(0);" onclick="resetFadedBackdrop();">' . __('Cancel and close this pop-up') . '</a>'; ?>
		</div>
	</form>
</div>