<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>

<?php if ($workspace != null && Wall::$currentType != Wall::TYPE_SPACE): ?>

    <?php echo Yii::t('ActivityModule.base', "<strong>%displayName%</strong> left the space <strong>%spaceName%</strong>", array(
        '%displayName%' => $user->displayName,
        '%spaceName%' => Helpers::truncateText($workspace->name, 40)
    )); ?>

<?php else: ?>

    <?php echo Yii::t('ActivityModule.base', "<strong>%displayName%</strong> left this space.", array(
        '%displayName%' => $user->displayName
    )); ?>

<?php endif; ?>

<?php $this->endContent(); ?>
