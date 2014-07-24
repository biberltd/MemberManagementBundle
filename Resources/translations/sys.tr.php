<?php
/**
 * sys.tr.php
 *
 * Bu dosya ilgili paketin sistem (hata ve başarı) mesajlarını Türkçe olarak barındırır.
 *
 * @vendor      BiberLtd
 * @package		Core\Bundles\MemberManagementBundle
 * @subpackage	Resources
 * @name	    sys.tr.php
 *
 * @author		Can Berkol
 *
 * @copyright   Biber Ltd. (www.biberltd.com)
 *
 * @version     1.0.0
 * @date        01.01.2014
 *
 * =============================================================================================================
 * !!! ÖNEMLİ !!!
 *
 * Çalıştığınız sunucu ortamına göre Symfony ön belleğini temizlemek için işbu dosyayı her değiştirişinizden sonra
 * aşağıdaki komutu çalıştırmalısınız veya app/cache klasörünü silmelisiniz. Aksi takdir de tercümelerde
 * yapmış olduğunuz değişiklikler işleme alıalınmayacaktır.
 *
 * $ sudo -u apache php app/console cache:clear
 * VEYA
 * $ php app/console cache:clear
 * =============================================================================================================
 * TODOs:
 * Yok
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
          'remind' => 'Şifreniz E-Posta adresinize gönderilmiştir.',
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