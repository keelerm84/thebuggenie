<?php TBGContext::loadLibrary('ui'); ?>
<?php if ($show_mode == 'all'): ?>
    <?php foreach ($build->getParent()->getBuilds() as $build): ?>
        <span id="build_list_<?php echo $build->getID(); ?>">
        <?php include_template('buildbox', array('build' => $build, 'access_level' => $access_level)); ?>
        </span>
    <?php endforeach; ?>
<?php elseif ($show_mode == 'one'): ?>
    <?php include_template('buildbox', array('build' => $build, 'access_level' => $access_level)); ?>
<?php endif; ?>