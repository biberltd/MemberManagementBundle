<?php
/**
 * InstallController
 *
 * This controller is used to install default / test values to the system.
 * The controller can only be accessed from allowed IP address.
 *
 * @package		MemberManagementBundleBundle
 * @subpackage	Controller
 * @name	    InstallController
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 *
 */

namespace BiberLtd\Bundle\MemberManagementBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpKernel\Exception,
    Symfony\Component\HttpFoundation\Response;

class InstallController extends Controller
{
    /** @var $locale            Holds the locale */
    protected $locale;
    /** @var $request           Holds the request object */
    protected $request;
    /** @var $session           Holds session object */
    protected $session;
    /** @var $translator        Holds the translator object */
    protected $translator;

    /**
     * @name 			init()
     *  				Each controller must call this function as its first statement.27
     *                  This function acts as a constructor and initializes default values of this controller.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     */
    protected  function init(){
//        if (isset($_SERVER['HTTP_CLIENT_IP'])
//            || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//            || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1', '192.168.1.134', '192.168.1.135', '176.33.138.112'))
//        ) {
//            header('HTTP/1.0 403 Forbidden');
//            exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//        }
        /** ****************** */
        $this->request = $this->getRequest();
        $this->session = $this->get('session');
        $this->locale = $this->request->getLocale();
        $this->translator = $this->get('translator');
    }
    /**
     * @name 			memberGroupAction()
     *  				DOMAIN/install/member_groups
     *                  Inserts detault member groups into database.
     *
     * @since			1.0.0
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @param           string          $key
     *
     * @return          Response
     *
     */
    public function memberGroupsAction($key)
    {
        /** Initialize */
        $this->init();

        if($key != 'bbr0609'){
            return new Response('You must provide the support key to run install functions.');
        }

        $model = $this->get('membermanagement.model');

        $now = new \DateTime('now', new \DateTimezone($this->container->getParameter('app_timezone')));

//        $group = array(
//            'code'          => 'support',
//            'date_added'    => $now,
//            'date_updated'  => $now,
//            'type'          => 's',
//            'count_members' => 0,
//            'site'          => 1,
//            'local' => array(
//                array('language' => 1,
//                      'name' => 'Biber Ltd. Destek Ekibi',
//                      'url_key' => 'biberltd_destek',
//                      'description' => 'Biber Ltd. destek ekibinin tanımlandığı gruptur.'
//                ),
//                array('language' => 2,
//                      'name' => 'Biber Ltd. Support Team',
//                      'url_key' => 'biberltd_support',
//                      'description' => 'This group is dedicated to Biber Ltd. support team.'
//                ),
//            )
//        );
        /**
         * Insert data into database.
         */
        $response = $model->insertMemberGroups(array($group));
        if($response['error']){
            return new Response('It seems like you have already run the install/member_groups command.');
        }
        $http_response = 'Default member group with the below information have been installed: <br>';

        $http_response .=
                 '<br><strong>code</strong>: '.$group['code']
                .'<br><strong>type</strong>: '.$group['type']
                .'<br><strong>count_members</strong>: '.$group['count_members']
                .'<br><strong>date_created</strong>: '.$group['date_added']->format('Y-m-d h:i:s')
                .'<br><strong>date_updated</strong>: '.$group['date_updated']->format('Y-m-d h:i:s')
                .'<br><strong>site id</strong>: '.$group['site']
                .'<br><strong>Localizations</strong>:';
        foreach($group['localizations'] as $localization){
            $http_response .=
                 '<br><strong>language</strong>: '.$localization['language']
                .'<br><strong>name</strong>: '.$localization['name']
                .'<br><strong>url_key</strong>: '.$localization['url_key']
                .'<br><strong>description</strong>: '.$localization['description'];
        }

        return new Response($http_response);
    }
    /**
     * @name 			membersAction()
     *  				DOMAIN/install/members
     *                  Inserts detault member details to database..
     *
     * @since			1.0.0
     * @version         1.0.1
     * @author          Can Berkol
     *
     * @param           string          $key
     *
     * @return          Response
     *
     */
    public function membersAction($key)
    {
        /** Initialize */
        $this->init();

        if($key != 'bbr0609'){
            return new Response('You must provide the support key to run install functions.');
        }
        $model = $this->get('membermanagement.model');

        $now = new \DateTime('now', new \DateTimezone($this->container->getParameter('app_timezone')));
        $member_support = array(
            'name_first'    => 'Biber',
            'name_last'     => 'Ltd.',
            'email'         => 'support@biberltd.com',
            'username'      => 'biberltd',
            'password'      => 'bbr0609',
            'date_registration' => $now,
            'date_activation'   => $now,
            'date_status_changed' => $now,
            'status'        => 'a',
            'language'      => 1,
            'site'          => 1,
            'groups'        => array(1),
            'local' => array(
                array(
                    'tr' => array(
                      'title' => 'Destek Ekibi Üyesi',
                    ),
                ),
                array(
                    'en' => array(
                      'title' => 'Support Team Member',
                    ),
                ),
            ),
        );
        $member_support = (object) $member_support;
//        $member_owner1 = array(
//            'name_first'    => 'Tansu',
//            'name_last'     => 'Mehmet',
//            'email'         => 'tansumtm@gmail.com',
//            'username'      => 'tansum',
//            'password'      => 't.M1234',
//            'date_registration' => $now,
//            'date_activation'   => $now,
//            'date_status_changed' => $now,
//            'status'        => 'a',
//            'language'      => 1,
//            'site'          => 1,
//            'groups'        => array(2),
//            'localizations' => array(
//                array('language' => 1,
//                      'title' => 'Proje Sahibi',
//                ),
//                array('language' => 2,
//                      'title' => 'Project Owner',
//                ),
//            ),
//        );
//        $member_owner1 = array(
//            'name_first'    => 'Yasemin',
//            'name_last'     => 'Aral',
//            'email'         => 'yaseminaral@pastelistanbul.com',
//            'username'      => 'yaseminaral',
//            'password'      => 'y.a@Pastel',
//            'date_registration' => $now,
//            'date_activation'   => $now,
//            'date_status_changed' => $now,
//            'status'        => 'a',
//            'language'      => 1,
//            'site'          => 1,
//            'groups'        => array(2),
//            'local' => array(
//                'tr' => array(
//                      'title' => 'Sistem Yöneticisi',
//                ),
//                'en' => array(
//                      'title' => 'System Manager',
//                ),
//            ),
//        );
//        $member_owner1 = (object) $member_owner1;

        /**
         * Insert data into database.
         */
        $response = $model->insertMembers(array($member_support));
        if($response['error']){
            return new Response('It seems like you have already run the install/members command.');
        }
        $http_response = 'Default members have been installed.';

        return new Response($http_response);
    }
}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Can Berkol
 * 01.01.2014
 * **************************************
 * U Methods are now protected with key: bbr0609
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.08.2013
 * **************************************
 * A Site Action
 *
 */