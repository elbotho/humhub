<?php $this->beginContent('application.modules_core.activity.views.activityLayout', array('activity' => $activity)); ?>                    
<?php
echo Yii::t('PostModule.base', '%displayName% created a new post.', array(
    '%displayName%' => '<strong>' . $user->displayName . '</strong>'
));
?>
<?php $this->endContent(); ?>
