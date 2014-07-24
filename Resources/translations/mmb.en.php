<?php
/**
 * mmb.en.php
 *
 * This file registers the bundle specific default translations.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagementBundle
 * @subpackage	Resources
 * @name	    mmb.en.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.2
 * @date        04.01.2014
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
return array(
    'form'    => array(
        'btn'       => array(
            'login'         => 'Login',
            'send'          => 'Send',
        ),
        'err'       => array(
            'minlength'     => array(
                'password'      => 'Password is too short. It needs to be at least 5 characters long.',
                'username'      => 'Username needs to be made of 3 or more characters.',
            ),
            'required'      => array(
                'captcha'       => 'You must enter the security code.',
                'password'      => 'You must enter your password.',
                'username'      => 'You must enter your username.',
                'username_or_email' => 'You must enter either your username or your e-mail.',
            )
        ),
        'lbl'       => array(
            'captcha'       => 'Security Code',
            'password'      => 'Password',
            'remember'      => 'Remember Me',
            'remindme'      => 'Forgot my credentials',
            'username'      => 'Username',
            'username_or_email'=> 'Username or E-mail',
        ),
        'title'     => array(
            'login'         => 'Login',
            'member_list'   => 'Registered Members',
            'remind'        => 'Remind My Credentials'
        ),
    ),
);
/**
 * Change Log
 * **************************************
 * v1.0.2                      Can Berkol
 * 04.01.2014
 * **************************************
 * A form.title.login
 * A form.title.member_list
 * A form.title.remind
 * U form.title
 *
 * **************************************
 * v1.0.1                      Can Berkol
 * 01.01.2014
 * **************************************
 * A form.btn.send
 * A form.err.required.username_or_email
 * A form.lbl.username_or_email
 *
 * **************************************
 * v1.0.0                      Can Berkol
 * 06.08.2013
 * **************************************
 * A form
 * A form.btn
 * A form.btn.login
 * A form.lbl
 * A form.lbl.captcha
 * A form.lbl.password
 * A form.lbl.remember
 * A form.lbl.remindme
 * A form.lbl.username
 * A form.title
 *
 */