<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\libs\ThemeHelper;
use humhub\models\UrlOembed;
use humhub\modules\admin\components\Controller;
use humhub\modules\user\libs\Ldap;

/**
 * SettingController
 *
 * @since 0.5
 */
class SettingController extends Controller
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'basic' => Yii::t('AdminModule.base', 'Basic'),
            'authentication' => Yii::t('AdminModule.base', 'Authentication'),
            'authentication-ldap' => Yii::t('AdminModule.base', 'Authentication'),
            'caching' => Yii::t('AdminModule.base', 'Caching'),
            'statistic' => Yii::t('AdminModule.base', 'Statistics'),
            'mailing' => Yii::t('AdminModule.base', 'Mailing'),
            'mailing-server' => Yii::t('AdminModule.base', 'Mailing'),
            'design' => Yii::t('AdminModule.base', 'Design'),
            'security' => Yii::t('AdminModule.base', 'Security'),
            'file' => Yii::t('AdminModule.base', 'Files'),
            'cronjobs' => Yii::t('AdminModule.base', 'Cronjobs'),
            'proxy' => Yii::t('AdminModule.base', 'Proxy'),
            'oembed' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'oembed-edit' => Yii::t('AdminModule.base', 'OEmbed providers'),
            'self-test' => Yii::t('AdminModule.base', 'Self test'),
        ]);
        return parent::init();
    }

    public function actionIndex()
    {
        return $this->redirect(['basic']);
    }

    /**
     * Basic Settings
     */
    public function actionBasic()
    {
        $form = new \humhub\modules\admin\models\forms\BasicSettingsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            // set flash message
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/basic']);
        }

        return $this->render('basic', array('model' => $form));
    }

    /**
     * Deletes Logo Image
     */
    public function actionDeleteLogoImage()
    {
        $this->forcePostRequest();
        $image = new \humhub\libs\LogoImage();

        if ($image->hasImage()) {
            $image->delete();
        }

        \Yii::$app->response->format = 'json';
        return [];
    }

    /**
     * Returns a List of Users
     */
    public function actionAuthentication()
    {
        $form = new \humhub\modules\admin\models\forms\AuthenticationSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
        }

        // Build Group Dropdown
        $groups = array();
        $groups[''] = Yii::t('AdminModule.controllers_SettingController', 'None - shows dropdown in user registration.');
        foreach (\humhub\modules\user\models\Group::find()->all() as $group) {
            if (!$group->is_admin_group) {
                $groups[$group->id] = $group->name;
            }
        }

        return $this->render('authentication', array('model' => $form, 'groups' => $groups));
    }

    public function actionAuthenticationLdap()
    {
        $form = new \humhub\modules\admin\models\forms\AuthenticationLdapSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/authentication-ldap']);
        }

        $enabled = false;
        $userCount = 0;
        $errorMessage = "";

        if (Yii::$app->getModule('user')->settings->get('auth.ldap.enabled')) {
            $enabled = true;
            try {
                $ldapAuthClient = new \humhub\modules\user\authclient\ZendLdapClient();
                $ldap = $ldapAuthClient->getLdap();
                $userCount = $ldap->count(
                        Yii::$app->getModule('user')->settings->get('auth.ldap.userFilter'), Yii::$app->getModule('user')->settings->get('auth.ldap.baseDn'), \Zend\Ldap\Ldap::SEARCH_SCOPE_SUB
                );
            } catch (\Zend\Ldap\Exception\LdapException $ex) {
                $errorMessage = $ex->getMessage();
            } catch (\Exception $ex) {
                $errorMessage = $ex->getMessage();
            }
        }

        return $this->render('authentication_ldap', array('model' => $form, 'enabled' => $enabled, 'userCount' => $userCount, 'errorMessage' => $errorMessage));
    }

    public function actionLdapRefresh()
    {
        Ldap::getInstance()->refreshUsers();
        return $this->redirect(['/admin/setting/authentication-ldap']);
    }

    /**
     * Caching Options
     */
    public function actionCaching()
    {
        $form = new \humhub\modules\admin\models\forms\CacheSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->cache->flush();

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved and flushed cache'));
            return $this->redirect(['/admin/setting/caching']);
        }

        $cacheTypes = array(
            'yii\caching\DummyCache' => Yii::t('AdminModule.controllers_SettingController', 'No caching (Testing only!)'),
            'yii\caching\FileCache' => Yii::t('AdminModule.controllers_SettingController', 'File'),
            'yii\caching\ApcCache' => Yii::t('AdminModule.controllers_SettingController', 'APC'),
        );

        return $this->render('caching', array('model' => $form, 'cacheTypes' => $cacheTypes));
    }

    /**
     * Statistic Settings
     */
    public function actionStatistic()
    {
        $form = new \humhub\modules\admin\models\forms\StatisticSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/statistic']);
        }

        return $this->render('statistic', array('model' => $form));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailing()
    {
        $form = new \humhub\modules\admin\models\forms\MailingDefaultsForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
        }

        return $this->render('mailing', array('model' => $form));
    }

    /**
     * E-Mail Mailing Settings
     */
    public function actionMailingServer()
    {
        $form = new \humhub\modules\admin\models\forms\MailingSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/mailing-server']);
        }

        $encryptionTypes = array('' => 'None', 'ssl' => 'SSL', 'tls' => 'TLS');
        $transportTypes = array('file' => 'File (Use for testing/development)', 'php' => 'PHP', 'smtp' => 'SMTP');

        return $this->render('mailing_server', array('model' => $form, 'encryptionTypes' => $encryptionTypes, 'transportTypes' => $transportTypes));
    }

    public function actionDesign()
    {
        $form = new \humhub\modules\admin\models\forms\DesignSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/design']);
        }

        $themes = [];
        foreach (ThemeHelper::getThemes() as $theme) {
            $themes[$theme->name] = $theme->name;
        }

        return $this->render('design', array('model' => $form, 'themes' => $themes, 'logo' => new \humhub\libs\LogoImage()));
    }

    /**
     * File Settings
     */
    public function actionFile()
    {
        $form = new \humhub\modules\admin\models\forms\FileSettingsForm;
        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_SettingController', 'Saved'));
            return $this->redirect(['/admin/setting/file']);
        }

        // Determine PHP Upload Max FileSize
        $maxUploadSize = \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('upload_max_filesize'));
        if ($maxUploadSize > \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'))) {
            $maxUploadSize = \humhub\libs\Helpers::GetBytesOfPHPIniValue(ini_get('post_max_size'));
        }
        $maxUploadSize = floor($maxUploadSize / 1024 / 1024);

        // Determine currently used ImageLibary
        $currentImageLibary = 'GD';
        if (Yii::$app->getModule('file')->settings->get('imageMagickPath'))
            $currentImageLibary = 'ImageMagick';

        return $this->render('file', array('model' => $form, 'maxUploadSize' => $maxUploadSize, 'currentImageLibary' => $currentImageLibary));
    }

    /**
     * Caching Options
     */
    public function actionCronjob()
    {
        return $this->render('cronjob', array());
    }

    /**
     * Proxy Settings
     */
    public function actionProxy()
    {
        $form = new \humhub\modules\admin\models\forms\ProxySettingsForm;


        if ($form->load(Yii::$app->request->post()) && $form->validate() && $form->save()) {
            Yii::$app->getSession()->setFlash('data-saved', Yii::t('AdminModule.controllers_ProxyController', 'Saved'));
            return $this->redirect(['/admin/setting/proxy']);
        }

        return $this->render('proxy', array('model' => $form));
    }

    /**
     * List of OEmbed Providers
     */
    public function actionOembed()
    {
        $providers = UrlOembed::getProviders();
        return $this->render('oembed', array('providers' => $providers));
    }

    /**
     * Add or edit an OEmbed Provider
     */
    public function actionOembedEdit()
    {

        $form = new \humhub\modules\admin\models\forms\OEmbedProviderForm;

        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            $form->prefix = $prefix;
            $form->endpoint = $providers[$prefix];
        }

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if ($prefix && isset($providers[$prefix])) {
                unset($providers[$prefix]);
            }
            $providers[$form->prefix] = $form->endpoint;
            UrlOembed::setProviders($providers);

            return $this->redirect(['/admin/setting/oembed']);
        }

        return $this->render('oembed_edit', array('model' => $form, 'prefix' => $prefix));
    }

    /**
     * Deletes OEmbed Provider
     */
    public function actionOembedDelete()
    {

        $this->forcePostRequest();
        $prefix = Yii::$app->request->get('prefix');
        $providers = UrlOembed::getProviders();

        if (isset($providers[$prefix])) {
            unset($providers[$prefix]);
            UrlOembed::setProviders($providers);
        }
        return $this->redirect(['/admin/setting/oembed']);
    }

    /**
     * Self Test
     */
    public function actionSelfTest()
    {
        return $this->render('selftest', array('checks' => \humhub\libs\SelfTest::getResults(), 'migrate' => \humhub\commands\MigrateController::webMigrateAll()));
    }

}
