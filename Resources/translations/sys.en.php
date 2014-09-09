<?php
/**
 * sys.en.php
 *
 * This file registers the bundle's system (error and success) messages in English.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagementBundle
 * @subpackage	Resources
 * @name	    sys.en.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        01.01.2014
 *
 * =============================================================================================================
 * !!! IMPORTANT !!!
 *
 * Depending your environment run the following code after you have modified this file to clear Symfony Cache.
 * Otherwise your changes will NOT take affect!
 *
 * $ sudo -u apache php app/console cache:clear
 * OR
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * None
 */
/** Nested keys are accepted */
return array(
    /** Error messages */
    'err'       => array(
        /** Member Management Model */
        'db'   => array(
            'notfound'      => array(
                'usernameoremail'     => 'The username/e-mail you have provided is not found in our database.',
            ),
        ),
    ),
    'scc' => array(
      'msg' => array(
          'remind' => 'Your password has been sent to your email address',
      )  
    ),
);
/**
 * Change Log
 * **************************************
 * v1.0.0                      Can Berkol
 * 01.01.2014
 * **************************************
 * A err
 * A err.db
 * A err.db.notfound
 * A err.db.notfound.usernameoremail
 */