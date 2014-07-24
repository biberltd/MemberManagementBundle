<?php
/**
 * AccountController
 *
 * account/ controller.
 *
 * @vendor      BiberLtd
 * @package		MemberManagementBundle
 * @subpackage	Controller
 * @name	    AccountController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.1
 * @date        26.12.2013
 *
 */

namespace BiberLtd\Core\Bundles\MemberManagementBundle\Controller;

use BiberLtd\Core\CoreController;
use Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

use BiberLtd\Core\Bundles\MemberManagementBundle\Services as MemberManagementService;

class AccountController extends CoreController
{
    /**
     * @todo Move this to project Bundles!!!!!
     *
     * @name            loginAction()
     *                  DOMAIN/{_locale}/manage/account/login
     *
     *                  Prepares the login page for the management module.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        /**
         * 1. Get global services and prepare URLs
         */
        $session = $this->get('session');
        $translator = $this->get('translator');
        $iv = $this->get('input_validator');
        $av = $this->get('access_validator');

        $this->setURLs();

        /**
         * 2. Validate Access Rights
         *
         * This controller is managed and only available to non-loggedin users.
         */
        $access_map = array(
            'unmanaged'     => false,
            'guest'         => true,
            'authenticated' => false,
            'members'       => array(),
            'groups'        => array(),
            'status'        => array()
        );
        if(!$av->has_access(null, $access_map)){
            /** If already logged-in redirect back to Manage/Dashboad */
            return new RedirectResponse($this->url['base_l'].'/manage/dashboard');
        }
        /**
         * 3. OPTIONAL :: ADDITIONAL PROCESSINGS
         */

        /** CSFR Prrotection */
        $csfr = md5(time());
        $session->set('_csfr', $csfr);

        /** Check if there is flash messages set */
        $flash = $this->prepareFlash($session);

        /**
         * 4. REQUIRED :: PREPARE TEMPLATE TAGS
         */
        $vars = array(
            'client'    => '',
            'flash'     => $flash,
            'form'      => array(
                'action'        =>  $this->url['base_l'].'/process/login',
                'btn'           => array(
                        'login'     => $translator->trans('form.btn.login', array(), 'mmb'),
                ),
                'lbl'           => array(
                    'captcha'   => $translator->trans('form.lbl.captcha', array(), 'mmb'),
                    'password'  => $translator->trans('form.lbl.password', array(), 'mmb'),
                    'remember'  => $translator->trans('form.lbl.remember', array(), 'mmb'),
                    'remindme'  => $translator->trans('form.lbl.remindme', array(), 'mmb'),
                    'username'  => $translator->trans('form.lbl.username', array(), 'mmb'),
                ),
                'method'        => 'post',
                'title'         => $translator->trans('form.lbl.title', array(), 'mmb'),
                'values'        => array(
                        'csfr'      => $csfr,
                ),
            ),
            'links'      => array(
                'remindme'   => $this->url['base_l'].'/account/recover',
            ),
            'page'      => array(
                'description'       => '',
                'keywords'          => '',
                'title'             => $translator->trans('page.mgmt.login.title', array(), 'page'),
            ),
            'site'      => array(
                'name'              => $translator->trans('site.name', array(), 'web'),
            ),
            'style'     => array(
                'body'      => array(
                    'classes'       => 'page-login',
                ),
            ),
        );
        /**
         * 5. REQUIRED :: MERGE PREPARED TAGS WITH DEFAULTS
         */
        $tags = $this->init_defaults(null, null, $vars, $theme = $this->get('kernel')->getContainer()->getParameter('admin_theme'));

        /**W
         * 6. REQUIRED :: RENDER PAGE
         *      note that if you do not want to immediately render the view to browser you need to use
         *      renderView() method instead of render() method.
         */
        return $this->render('BiberLtdCoreBundlesCoreBundle:Default:page_login.html.smarty', $tags);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.0                      Can Berkol
 * 11.08.2013
 * **************************************
 * A logoutAction()
 *
 */
