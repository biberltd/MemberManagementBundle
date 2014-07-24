<?php
/**
 * RenderController
 *
 * Used to process HTTP request that requires a landing page and that redirects the user based on the process status.
 *
 * @vendor      BiberLtd
 * @package		MemberManagementBundle
 * @subpackage	Controller
 * @name	    RenderController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.2
 * @date        04.01.2014
 *
 */

namespace BiberLtd\Core\Bundles\MemberManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

class RenderController
{
    private $templating;
    private $theme;

    public  function __construct(EngineInterface $templating){
        $this->templating = $templating;
    }
    /**
     * @name 			renderLoginForm()
     *  				Renders login form based on given specifications.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     * @param           array           $form
     *                                      action
     *                                      btn
     *                                          login
     *                                      lbl
     *                                          captcha
     *                                          remember
     *                                          remindme
     *                                          password
     * @param           array           $settings
     *                                      captcha
     *                                      remember
     *                                      remind
     * @param           array           $message
     *                                      flash
     *                                          exist
     *                                          message
     * @param           array           $links
     *                                      logo
     *                                      name
     * @param           array           $client
     *                                      light
     *                                      dark
     * @param           array           $icons
     * @param           array           $cms
     *                                      logo
     *                                      name
     *                                      version
     *
     * @return          array           $response
     */
    public function renderLoginForm($core, $form, $settings, $message, $links = array(), $client = array(), $icons = array(), $cms = array()){
        $translator = new Translator($core['locale']);

        $csfr = md5(time());
        $this->url = $core['url'];
        /**
         * Prepare defaults
         */
        $formDefaults = array(
            'action'    => $this->url['base_l'].'/process/login',
            'btn'       => array(
                'login'     => $translator->trans('form.btn.login', array(), 'mmb'),
            ),
            'lbl'       => array(
                'captcha'   => $translator->trans('form.lbl.captcha', array(), 'mmb'),
                'remember'  => $translator->trans('form.lbl.remember', array(), 'mmb'),
                'remindme'  => $translator->trans('form.lbl.remindme', array(), 'mmb'),
                'password'  => $translator->trans('form.lbl.password', array(), 'mmb'),
            ),
            'method'    => 'post',
            'title'     => $translator->trans('form.title.login', array(), 'mmb'),
            'values'    => array(
                'csfr'      => $csfr,
            ),
        );
        $settingsDefault = array(
            'captcha'   => false,
            'remember'  => true,
            'remind'    => true,
        );
        $clientDefaults = array(
            'name'      => 'Biber Ltd.',
            'logo'      => $this->url['cdn'].'/site/logo/logo.jpg',
        );
        $iconDefaults = array(
            'light'     => array(
                'user'      => $this->url['themes'].'/'.$core['theme'].'/img/icons/light/user.png',
            ),
        );
        $cmsDefaults = array(
            'logo'      => $this->url['themes'].'/'.$core['theme'].'/css/img/bbr-icon.png',
            'name'      => 'Biber Ltd. CMS',
            'version'   => '1.0.0',
        );
        $messageDefaults = array(
            'flash'     => array(
                'exist'     => false,
            )
        );
        $linkDefaults = array(
            'remindme'  => $this->url['base_l'].'manage/account/remind',
        );
        /**
         * Merge values and set template variables.
         */
        $vars = array(
            'cms'    => array_merge($cmsDefaults, $cms),
            'module' => array(
                'client'    => array_merge($clientDefaults, $client),
                'form'      => array_merge($formDefaults, $form),
                'icons'     => array_merge($iconDefaults, $icons),
                'links'     => array_merge($linkDefaults, $links),
                'message'   => array_merge($messageDefaults, $message),
                'settings'  => array_merge($settingsDefault, $settings)
            ),
        );
        return $this->templating->render('BiberLtdCoreBundlesMemberManagementBundle:Modules:form.login.html.smarty', $vars);
    }
    /**
     * @name 			renderRemindForm()
     *  				Renders login form based on given specifications.
     *
     * @since			1.0.1
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @param           array           $form
     *                                      action
     *                                      btn
     *                                          send
     *                                      lbl
     *                                          username_or_email
     * @param           array           $settings
     *                                      ** empty array **
     * @param           array           $message
     *                                      flash
     *                                          exist
     *                                          message
     * @param           array           $links
     * @param           array           $client
     *                                      logo
     *                                      name
     * @param           array           $icons
     *                                      light
     *                                      dark
     * @param           array           $cms
     *                                      logo
     *                                      name
     *                                      version
     *
     * @return          array           $response
     */
    public function renderRemindForm($core, $form, $settings, $message, $links = array(), $client = array(), $icons = array(), $cms = array()){
        $translator = new Translator($core['locale']);
        $csfr = md5(time());
        $this->url = $core['url'];
        /**
         * Prepare defaults
         */
        $formDefaults = array(
            'action'    => $this->url['base_l'].'/process/remind_password',
            'btn'       => array(
                'send'     => $translator->trans('form.btn.send', array(), 'mmb'),
            ),
            'lbl'       => array(
                'username_or_email'   => $translator->trans('form.lbl.username_or_email', array(), 'mmb'),
            ),
            'method'    => 'post',
            'title'     => $translator->trans('form.title.remind', array(), 'mmb'),
            'values'    => array(
                'csfr'      => $csfr,
            ),
        );
        $settingsDefault = array();
        $clientDefaults = array(
            'name'      => 'Biber Ltd.',
            'logo'      => $this->url['cdn'].'/site/logo/logo.jpg',
        );
        $iconDefaults = array(
            'light'     => array(
                'user'      => $this->url['themes'].'/'.$core['theme'].'/img/icons/light/user.png',
            ),
        );
        $cmsDefaults = array(
            'logo'      => $this->url['themes'].'/'.$core['theme'].'/css/img/bbr-icon.png',
            'name'      => 'Biber Ltd.',
            'version'   => '1.0.0',
        );
        $messageDefaults = array(
            'flash'     => array(
                'exist'     => false,
            )
        );
        $linkDefaults = array(
            'login'  => $this->url['base_l'].'manage/account/login',
        );
        /**
         * Merge values and set template variables.
         */
        $vars = array(
            'cms'    => array_merge($cmsDefaults, $cms),
            'module' => array(
                'client'    => array_merge($clientDefaults, $client),
                'form'      => array_merge($formDefaults, $form),
                'icons'     => array_merge($iconDefaults, $icons),
                'links'     => array_merge($linkDefaults, $links),
                'message'   => array_merge($messageDefaults, $message),
                'settings'  => array_merge($settingsDefault, $settings)
            ),
        );

        return $this->templating->render('BiberLtdCoreBundlesMemberManagementBundle:Modules:form.remind.html.smarty', $vars);
    }
    /**
     * @name 			renderMemberList()
     *  				Renders member list view.
     *
     * @since			1.0.2
     * @version         1.0.2
     * @author          Can Berkol
     *
     * @see             BiberLtd\Core\Bundles\CoreBundle\Controller\RenderController renderDataTable()
     *
     * @param           array           $data
     * @param           array           $core
     * @param           string          $title
     * @param           array           $headers
     * @param           array           $txt
     * @param           array           $settings
     * @param           integer         $tabIndex
     * @param           array           $options
     *
     * @return          array           $response
     */
    public function renderMemberList($data, $core, $title = '', $headers = array(), $txt = array(), $settings = array(), $tabIndex = 88788, $options = array()){
        $translator = new Translator($core['locale']);

        $this->url = $core['url'];
        /**
         * Prepare defaults & merge
         */
        if(empty($title)){
            $title = $translator->trans('form.title.member_list');
        }
        $headersDefault = array(
            array('code' => 'username', 'name' => $translator->trans('form.lbl.username', array(), 'mmb')),
            array('code' => 'email', 'name' => $translator->trans('form.lbl.email', array(), 'mmb')),
            array('code' => 'dateRegistration', 'name' => $translator->trans('form.lbl.date_registration', array(), 'mmb')),
            array('code' => 'dateLastLogin', 'name' => $translator->trans('form.lbl.date_last_login', array(), 'mmb')),
        );

        $headers = array_merge($headersDefault, $headers);

        $optionDefaults = array(
            array('option' => 'delete', 'value' => $translator->trans('form.lbl.delete', array(), 'mmb')),
        );

        $options = array_merge($optionDefaults, $options);

        $data['items'] = $data;
        $data['headers'] = $headers;
        $data['options'] = $options;

        $settingsDefaults = array(
            'ajax'          => true,
            'column'        => array(
                'width'         => '21px',
            ),
            'editable'      => true,
            'info'          => true,
            'filter'        => true,
            'lengthChange'  => true,
            'link'          => array(
                'order'         => $this->url['base_l'].'/',
                'source'        => $this->url['base_l'].'/',
            ),
            'method'        => 'POST',
            'orderWithAjax' => true,
            'paginate'      => true,
            'paginationType'=> true,
            'processing'    => true,
            'row'           => array(
                'count'         => 100,
                'show'          => 10,
                'start'         => 1,
            ),
            'rowReordering' => false,
            'sorting'       => true,
            'state'         => true,
            'table'         => array(
                'height'        => '',
                'scrollCollapse'=> true,
                'width'         => '',
            ),
            'viewport'      => false,
        );

        $settings = array_merge($settingsDefaults, $settings);

        $txtDefaults = array(
            'btn'           => array(
                'edit'          => $translator->trans('btn.edit', array(), 'datatable'),
            ),
            'lbl'           => array(
                'find'          => $translator->trans('lbl.find', array(), 'datatable'),
                'first'         => $translator->trans('lbl.first', array(), 'datatable'),
                'info'          => $translator->trans('lbl.info', array(), 'datatable'),
                'last'          => $translator->trans('lbl.last', array(), 'datatable'),
                'limit'         => $translator->trans('lbl.limit', array(), 'datatable'),
                'next'          => $translator->trans('lbl.next', array(), 'datatable'),
                'prev'          => $translator->trans('lbl.prev', array(), 'datatable'),
                'processing'    => $translator->trans('lbl.processing', array(), 'datatable'),
                'recordNotFound'=> $translator->trans('lbl.not_found', array(), 'datatable'),
                'noRecords'     => $translator->trans('lbl.no_records', array(), 'datatable'),
                'numberOfRecords'=> $translator->trans('lbl.number_of_records', array(), 'datatable'),
            ),
        );

        $txt = array_merge($txtDefaults, $txt);

        $coreRenderService = $this->get('corerender.model');

        return $coreRenderService->renderDataTable($data, $core, $title, $txt, $settings, $tabIndex);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Can Berkol
 * 01.01.2014
 * **************************************
 * A renderRemindForm()
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 25.12.2013
 * **************************************
 * A renderLoginForm()
 *
 */