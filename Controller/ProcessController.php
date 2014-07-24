<?php
/**
 * ProcessController
 *
 * Used to process HTTP request that requires a landing page and that redirects the user based on the process status.
 *
 * @package		MemberManagementBundle
 * @subpackage	Controller
 * @name	    ProcessController
 *
 * @author		Can Berkol
 * @author      Said İmamoğlu
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.3
 *
 */

namespace BiberLtd\Core\Bundles\MemberManagementBundle\Controller;

use BiberLtd\Core\CoreController;
use Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class ProcessController extends CoreController
{
    /**
     * @name            loginAction()
     *                  DOMAIN/{_locale}/process/login
     *
     *                  Prepares the login page for the management module.
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.0
     *
     * @param           Request         $request
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     *
     * @todo            Bind with model.
     */
    public function loginAction($redirect, Request $request){
        /**
         * 1. Get:
         *      access validator, input validator, referrer, session, session manager, translator
         */
        $translator = $this->get('translator');
        $session = $this->get('session');
        $referrer = $request->headers->get('referer');
        $iv = $this->get('input_validator');
        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        /** Set URLs. */
        $this->setURLs();
        $redirect = str_replace('-', '/', $redirect);
        /**
         * 2. Get form data
         */
        $formData = $request->get('login');
        $iv->set_input(array($formData));
        if(!$iv->is_array()){
            /** @todo multi-site logging */
            $sm->logAction('login.fail.invalid.data', 1);
            /** If $formData is not an array or if there is no request with 'login' key then redirect back with flash message. */
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.data.login', array(), 'sys'));
            return new RedirectResponse($referrer);
        }
        if(!isset($formData['csfr']) || $formData['csfr'] != $session->get('_csfr')){
            /** @todo multi-site logging */
            $sm->logAction('login.fail.csfr', 1);
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.csfr', array(), 'sys'));
            return new RedirectResponse($referrer);
        }
        /** Get form submitted values */
        $username = $formData['username'];
        $password = $formData['password'];
        /** Validate data using input validator */
        $iv->set_input(array($username, $password));
        /**
         * Both username and password must be filled in
         */
        if($iv->is_empty('or')){
            /** Login Failure, Redirect Back */
            /** @todo multi-site logging */
            $what = array();
            if(empty($username)){
                $what[] = 'username';
            }
            if(empty($password)){
                $what[] = 'password';
            }
            $what = json_encode($what);
            $sm->logAction('login.fail.empty.data', 1, $what);
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.required.all', array(), 'sys'));
            return new RedirectResponse($referrer);
        }
        $authenticated = $sm->authenticate($username, $password);
        if(!$authenticated){
            /** @todo multi-site logging */
            $sm->logAction('login.fail.invalid.credentials', 1, json_encode(array('username' => $username)));
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.credentials', array(), 'sys'));
            return new RedirectResponse($referrer);
        }

        /**
         * Get Member Details & Run against Security features
         */

        /** Before setting session we need to check if the user belongs to the current site */
        /**
         * @todo Multi site management
         */
        if(!in_array($this->get('kernel')->getContainer()->getParameter('site_id'), $sm->get_detail('sites'))){
            /** @todo multi-site logging */
            $details = json_decode(array('member_sites' => $sm->get_detail('sites')));
            $sm->logAction('login.fail.wrong.site', $this->get('kernel')->getContainer()->getParameter('site_id'), $details);
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.login.wrong_site', array(), 'sys'));
            return new RedirectResponse($referrer);
        }

        /**
         * Check if member has enough access rights
         * @todo AccessManagementBundle ile birleştirilecek
         */
        $memberGrantedActions = $sm->getDetail('granted_actions');
        $access_map = array(
            'unmanaged' => false,
            'groups'    => array('support', 'admin', 'founder', 'manage'),
            'guest'     => false,
            'members'   => array(),
            'authenticated' => true,
            'status'    => array('a'),
        );
        if(!$av->has_access(null, $access_map) && !$av->isActionGranted('manage.access')){
            /** @todo multi-site logging */
            $sm->logAction('processor.direct.access', 1, 'loginAction');
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.rights', array(), 'sys'));
            return new RedirectResponse($referrer);
        }
        /** Set cookie & prepare redirect response */
        // $http_response = new RedirectResponse($url_base_l.'/manage/dashboard');
        $http_response = new RedirectResponse($this->url['base_l'].'/'.$redirect);
        /** If remember me is checked the cookie expiration date will be set to + persisting_session_days days */
        if(isset($formData['remember']) && $formData['remember'] == 'remember'){
            $date = new \DateTime('now', new \DateTimeZone($this->get('kernel')->getContainer()->getParameter('app_timezone')));
            $date->add(new \DateInterval('P'.$this->get('kernel')->getContainer()->getParameter('persisting_session_days').'D'));
            $http_response->headers->setCookie(new Cookie('bbr_member', $session->get('authentication_data'), $date, '/', null, false, false));
        }
        else{
            $date = new \DateTime('now', new \DateTimeZone($this->get('kernel')->getContainer()->getParameter('app_timezone')));
            $date->add(new \DateInterval('P1D'));
            $http_response->headers->setCookie(new Cookie('bbr_member', $session->get('authentication_data'), $date, '/', null, false, false));
        }
        $sm->update('login');
        /** @todo multi-site logging */
        $sm->logAction('login.success', 1);
        return $http_response;
    }
    /**
     * @name            logoutAction()
     *                  DOMAIN/{_locale}/process/logout
     *
     *                  The default logout process. Logs a logged-in user out, destroys the associated session and
     *                  redirects the user to given address..
     *
     * @author          Can Berkol
     * @since           1.0.0
     * @version         1.0.3
     *
     * @param           string      $backto
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function logoutAction($backto = 'account-login')
    {
        /**
         * 1. REQUIRED :: GET TRANSLATOR
         *    REQUIRED :: GET SESSION
         *    REQUIRED :: SET BASE URL
         *    REQUIRED :: SERVICE SESSION MANAGER
         *    REQUIRED :: SERVICE ACCESS VALIDATOR
         *    REQUIRED :: GET REFERRER
         */
        $translator = $this->get('translator');
        $session = $this->get('session');

        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        $this->setURLs();

        /**
         * Check if member has enough access rights
         * @todo AccessManagementBundle ile birleştirilecek
         */
        $access_map = array(
            'unmanaged' => false,
            'groups'    => array(),
            'guest'     => false,
            'members'   => array(),
            'authenticated' => true,
            'status'    => array('a'),
        );
        $backto = '/'.str_replace('-', '/', $backto);
        if(!$av->has_access(null, $access_map, true)){
            $sm->logAction('processor.direct.access', 1, 'logoutAction');
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            /** $response[$code] must have a corresponding translation */
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.not.logged_in', array(), 'sys'));
            return new RedirectResponse($this->url['base_l'].$backto);
        }

        $sm->logAction('logout.success', 1);
        $sm->update('logout');
        $sm->logout();

        $response = new RedirectResponse($this->url['base_l'].$backto);
        $response->headers->clearCookie('bbr_member');

        /** @todo multi-site */

        return $response;
    }
    /**
     * @name            remindAction()
     *                  DOMAIN/{_locale}/process/remind
     *
     *                  Prepares the login page for the management module.
     *
     * @author          Said İmamoğlu
     * @since           1.0.2
     * @version         1.0.2
     *
     * @param           Request         $request
     *
     * @return          \Symfony\Component\HttpFoundation\Response
     *
     * @todo            Bind with model.
     */
    public function remindAction($redirect, Request $request){
        /**
         * 1. Get:
         *      access validator, input validator, referrer, session, session manager, translator
         */
        $translator = $this->get('translator');
        $session = $this->get('session');
        $referrer = $request->headers->get('referer');
        $iv = $this->get('input_validator');
        $encryption = $this->get('encryption');

        $av = $this->get('access_validator');
        $sm = $this->get('session_manager');

        /** Set URLs. */
        $this->setURLs();
        $redirect = str_replace('-', '/', $redirect);
        /**
         * 2. Get form data
         */
        $formData = $request->get('remember');
        $iv->set_input(array($formData));
        if(!$iv->is_array()){
            /** @todo logAction :: login.admin.csfr */
            /** If $formData is not an array or if there is no request with 'login' key then redirect back with flash message. */
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.data.login', array(), 'sys'));
            return new RedirectResponse($referrer);
        }
        if(!isset($formData['csfr']) || $formData['csfr'] != $session->get('_csfr')){
            /** @todo logAction :: login.admin.csfr */
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.invalid.csfr', array(), 'sys'));
            return new RedirectResponse($referrer);
        }

        /** Get form submitted values */
        $userNameOrEmail = $formData['usernameoremail'];

        /** is this an email or username */
        $iv->set_input(array($userNameOrEmail));
        /**
         * Both username and password must be filled in
         */
        $isEmail = $iv->is_email();
        /** Get MemberManagementModel */
        $memberModel = $this->get('membermanagement.model');

        if($isEmail){
            $response = $memberModel->getMember($userNameOrEmail, 'email');
        }
        else{
            $response = $memberModel->getMember($userNameOrEmail, 'username');
        }

        /**
         * If username or email is not found return to remind page with flash messages.
         */
        if($response['error']){
            /**
             * @todo Add ActionLog :: account_remind_fail
             */
            $session->getFlashBag()->add('msg.status', true);
            $session->getFlashBag()->add('msg.type', 'error');
            $session->getFlashBag()->add('msg.content', $translator->trans('msg.error.db.notfound.usernameoremail', array(), 'sys'));
            return new RedirectResponse($referrer);
        }

        $member = $response['result']['set'];

        $password = $encryption->input($member->getPassword())->key($this->get('kernel')->getContainer()->getParameter('app_key'))->decrypt('enc_reversible_pkey')->output();

        /**
         * @todo send password as emailA
         */

        $http_response = new RedirectResponse($this->url['base_l'].'/'.$redirect);

        return $http_response;
    }
}
/**
 * Change Log
 * **************************************
 * v1.0.2                      Can Berkol
 * 01.01.2014
 * **************************************
 * U logoutAction()
 *
 * **************************************
 * v1.0.2                   Said İmamoğlu
 * 01.01.2014
 * **************************************
 * A remindAction()
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 31.12.2013
 * **************************************
 * A loginAction()
 *
 */
