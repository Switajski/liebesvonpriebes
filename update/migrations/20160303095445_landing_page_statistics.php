<?php
/**
 * landing page statistics
 *
 * @author ms
 * @created Thu, 03 Mar 2016 09:54:45 +0100
 */

/**
 * Class Migration_20160303095445
 */
class Migration_20160303095445 extends Migration implements IMigration
{
    protected $author = 'ms';

    public function up()
    {
        $this->execute("INSERT INTO `tadminrecht` (`cRecht`, `cBeschreibung`, `kAdminrechtemodul`) VALUES ('STATS_LANDINGPAGES_VIEW', 'Einstiegsseiten', '10');");
    }

    public function down()
    {
        $this->execute("DELETE FROM `tadminrecht` WHERE `cRecht`='STATS_LANDINGPAGES_VIEW';");
    }
}
