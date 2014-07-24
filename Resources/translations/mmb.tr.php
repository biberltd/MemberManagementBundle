<?php
/**
 * mmb.tr.php
 *
 * This file registers the bundle's default translation values
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagementBundle
 * @subpackage	Resources
 * @name	    mmb.tr.php
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
            'login'         => 'Giriş',
            'send'          => 'Gönder',
        ),
        'err'       => array(
            'minlength'     => array(
                'password'      => 'Şifre en az 5 karakterden oluşmaktadır.',
                'username'      => 'Kullanıcı adları en az 3 karakterden oluşmaktadır.',
            ),
            'required'      => array(
                'captcha'       => 'Güvenlik kodunu girmelisiniz.',
                'password'      => 'Şifre girmelisiniz.',
                'username'      => 'Kullanıcı adı girmelisiniz.',
                'username_or_email' => 'Kullanıcı adınızı veya e-posta adresinizi girmelisiniz.',
            )
        ),
        'lbl'       => array(
            'captcha'       => 'Güvenlik Kodu',
            'password'      => 'Şifre',
            'remember'      => 'Bilgilerimi Hatırla',
            'remindme'      => 'Şifremi Unuttum',
            'username'      => 'Kullanıcı Adı',
            'username_or_email'=> 'Kullanıcı Adı veya E-Posta Adresi',
        ),
        'title'     => array(
            'login'         => 'Giriş Formu',
            'member_list'   => 'Üyeler',
            'remind'        => 'Şifremi Unuttum',
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
 * 26.12.2013
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