<?php
/**
 * increase_session_length_to_128
 *
 * @author sh
 * @created Thu, 03 Mar 2016 09:57:36 +0100
 */

/**
 * Class Migration_20160303095736
 */
class Migration_20160303095736 extends Migration implements IMigration
{
    protected $author = 'sh';

    public function up()
    {
        $this->execute("ALTER TABLE tbestellung CHANGE `cSession` `cSession` VARCHAR(128) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tzahlungsession CHANGE `cSID` `cSID` VARCHAR(128) NOT NULL DEFAULT ''");
        // columns were varchar(255). php only supports a session id length of 128 characters. see http://php.net/manual/de/function.session-id.php#116836
        $this->execute("ALTER TABLE tzahlungsid CHANGE `cId` `cId` VARCHAR(128) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tbesucher CHANGE `cSessId` `cSessId` VARCHAR(128) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tadminsession CHANGE `cSessionId` `cSessionId` VARCHAR(128) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tsession CHANGE `cSessionId` `cSessionId` VARCHAR(128) NOT NULL DEFAULT ''");
    }

    public function down()
    {
        $this->execute("ALTER TABLE tbestellung CHANGE `cSession` `cSession` VARCHAR(33) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tzahlungsession CHANGE `cSID` `cSID` VARCHAR(33) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tzahlungsid CHANGE `cId` `cId` VARCHAR(255) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tbesucher CHANGE `cSessId` `cSessId` VARCHAR(255) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tadminsession CHANGE `cSessionId` `cSessionId` VARCHAR(255) NOT NULL DEFAULT ''");
        $this->execute("ALTER TABLE tsession CHANGE `cSessionId` `cSessionId` VARCHAR(255) NOT NULL DEFAULT ''");
    }
}
