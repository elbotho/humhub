<?php $this->beginContent('application.modules_core.notification.views.notificationLayout', array('notification' => $notification, 'iconClass' => 'fa fa-minus-circle approval declined')); ?>
<?php echo Yii::t('SpaceModule.notifications', '<strong>{userName}</strong> declined your membership request for the space <strong>{spaceName}</strong>', array(
    '{userName}' => $creator->displayName,
    '{spaceName}' => $targetObject->name)); ?>
<?php $this->endContent(); ?>