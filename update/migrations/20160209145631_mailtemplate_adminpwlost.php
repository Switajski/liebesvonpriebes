<?php
/**
 * mailtemplate_adminpwlost
 *
 * @author dh
 * @created Tue, 09 Feb 2016 14:56:31 +0100
 */

/**
 * Class Migration_20160209145631
 */
class Migration_20160209145631 extends Migration implements IMigration
{
    protected $author = 'dh';

    public function up()
    {
        //update system-default mailtemplate
        $this->execute('DELETE FROM `temailvorlagespracheoriginal` WHERE kEmailvorlage=29;');
        $this->execute('INSERT INTO `temailvorlagespracheoriginal` (`kEmailvorlage`,`kSprache`,`cBetreff`,`cContentHtml`,`cContentText`,`cPDFS`,`cDateiname`) VALUES (29,1,\'Passwort vergessen bei #firma.name#\',\'Hallo,<br>\r\nfür Ihren Account wurde ein neues Passwort angefordert.\r\nÖffnen Sie zum Bestätigen bitte den folgenden Link:<br>\r\n<br>\r\n<a href=\"{$passwordResetLink}\" target=\"_blank\">{$passwordResetLink}</a><br>\',\'Hallo,\r\n\r\nfür Ihren Account wurde ein neues Passwort angefordert.\r\nÖffnen Sie zum Bestätigen bitte den folgenden Link:\r\n\r\n{$passwordResetLink}\r\n\',\'\',\'\');');
        $this->execute('INSERT INTO `temailvorlagespracheoriginal` (`kEmailvorlage`,`kSprache`,`cBetreff`,`cContentHtml`,`cContentText`,`cPDFS`,`cDateiname`) VALUES (29,2,\'Lost your password on #firma.name#\',\'Hello,<br>\r\na new password was requested for your account.<br>\r\nPlease click the following link to reset your password:<br>\r\n<br>\r\n<a href=\"{$passwordResetLink}\" target=\"_blank\">{$passwordResetLink}</a><br>\r\n                                              \',\'Hello,\r\n\r\na new password was requested for your account.\r\nPlease click the following link to reset your password:\r\n\r\n{$passwordResetLink}\r\n\',\'\',\'\');');

        //update userdefined mailtemplate
        $this->execute("UPDATE temailvorlagesprache userdef JOIN temailvorlagespracheoriginal orig ON orig.kEmailvorlage=userdef.kEmailvorlage AND orig.kSprache=userdef.kSprache SET userdef.cBetreff=orig.cBetreff, userdef.cContentHTML=orig.cContentHTML, userdef.cContentText=orig.cContentText WHERE userdef.kEmailvorlage=29 AND userdef.cBetreff='';");
    }

    public function down()
    {
        $this->execute("DELETE FROM `temailvorlagespracheoriginal` WHERE kEmailvorlage=29;");
    }
}
