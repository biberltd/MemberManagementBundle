<?php
/**
 * TestController
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
    Symfony\Component\HttpFoundation\Response,
    BiberLtd\Core\CoreController;

class TestController extends CoreController{
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
    protected function init(){
        $this->init_defaults();
        if (isset($_SERVER['HTTP_CLIENT_IP'])
            || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            || !in_array(@$_SERVER['REMOTE_ADDR'], unserialize(APP_DEV_IPS))
        ) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }
        /** ****************** */
        $this->request = $this->getRequest();
        $this->session = $this->get('session');
        $this->locale = $this->request->getLocale();
        $this->translator = $this->get('translator');
    }
    /**
     * @name 			memberLocalizationAction()
     *  				DOMAIN/test/memberLocalizations
     *                  Used to test member localizations.
     *
     * @since			1.0.0
     * @version         1.0.0
     * @author          Can Berkol
     *
     */
    public function memberLocalizationsAction()
    {
        /** Initialize */
        $this->init();

        $model = $this->get('core_member_management_bundle.model');

        $now = new \DateTime('now', new \DateTimezone($this->container->getParameter('app_timezone')));

        $sort_order = array('username' => 'ASC');
        $filter = null; //array('username' => array('contains' => array('biber')));

        $limit = null; //array('start' => 0, 'count' => 1);
        $response = $model->list_members($filter, $sort_order, $limit);
        $to_update = array();
        if(!$response['error']){
            $members = $response['result']['set'];
            foreach($members as $member){
                $member->get_localization('en')->setTitle(str_replace('test1', '', $member->get_localization('en')->getTitle()));
                echo $member->get_localization('en')->getTitle().'<br>';
                $to_update[] = $member;
            }
            $model->update_members($to_update);
            $html = '<html><head></head><body>'.$response['result']['total_rows'].'</body></html>';
            return new Response($html);
        }
        $html = '<html><head></head><body>Not found!</body></html>';
        return new Response($html);
    }

}