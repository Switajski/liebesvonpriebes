<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class PreisverlaufGraph
 */
class PreisverlaufGraph
{
    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var int
     */
    public $nDiffStamp; // Zeitdifferenz (Unix Timestamp) zwischen dem ältesten Preisverlaufseintrag und dem jüngsten
    public $nAnzahlPreise; // Anzahl an Preisen für die Y-Achsen Legende
    public $nAnzahlTage; // Anzahl Tage in der es Preisverlaufseinträge für den Aritkel gibt
    public $nStepX; // Aktueller Schritt für den jeweiligen X-Achsen Legendeneintrag
    public $nStepY; // Aktueller Schritt für den jeweiligen Y-Achsen Legendeneintrag
    public $nHoehe; // Maximale Höhe des Bildes
    public $nBreite; // Maximale Breite des Bildes
    public $nStep; // Der Preisschritt für die Y-Achsen Legendenbeschriftung
    public $nMaxTimestamp; // Jüngster Preisverlaufseintrag (Unix Timestamp)
    public $nMinTimestamp; // Ältester Preisverlaufseintrag (Unix Timestamp)
    public $nSchriftgroesse; // Schriftgröße für die Legendenbeschriftung bzw. allen Schriften im Bild

    // Paddings
    public $nPolsterLinks; // Linke Polster zwischen dem Bildanfang und der äusseren Box
    public $nPolsterRechts; // Rechte Polster zwischen dem Bildanfang und der äusseren Box
    public $nPolsterOben; // Oberes Polster zwischen dem Bildanfang und der äusseren Box
    public $nPolsterUnten; // Unteres Polster zwischen dem Bildanfang und der äusseren Box
    public $nInternPolsterX; // X-Achsen Polster zwischen der äusseren Box und der inneren Box
    public $nInternPolsterY; // Y-Achsen Polster zwischen der äusseren Box und der inneren Box

    // Signi Punkte
    public $nBreiteRahmen; // Breite der äusseren Box
    public $nHoeheRahmen; // Höhe der äusseren Box
    public $nInternPolsterXPixel; // X-Achsen Polster zwischen der äusseren Box und der inneren Box in Pixel
    public $nInternPolsterYPixel; // Y-Achsen Polster zwischen der äusseren Box und der inneren Box in Pixel
    public $nInnenRahmenBreite; // Breite der inneren Box
    public $nInnenRahmenHoehe; // Höhe der inneren Box
    public $nAussenRahmenOben; // Y-Position der oberen Aussenbox
    public $nAussenRahmenLinks; // X-Position der linken Aussenbox
    public $nAussenRahmenUnten; // Y-Position der unteren Aussenbox
    public $nAussenRahmenRechts; // X-Position der rechten Aussenbox
    public $nInnenRahmenOben; // Y-Position der oberen Innenbox
    public $nInnenRahmenLinks; // X-Position der linken Innenbox
    public $nInnenRahmenUnten; // Y-Position der unteren Innenbox
    public $nInnenRahmenRechts; // X-Position der rechten Innenbox

    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var float
     */
    public $fMaxPreis; // Größter Preis vom aktuellen Preisverlauf
    public $fMinPreis; // Kleinster Preis vom aktuellen Preisverlauf
    public $fDiffPreis; // Differenz zwischen dem kleinsten und größten Preisverlaufspreis
    public $fStepWert_arr; // Array von Preissteps für die Berechnung der Y-Achsen Legende

    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var string
     */
    public $cSchriftart; // Schriftart für die Legendenbeschriftung bzw. allen Schriften im Bild
    public $cSchriftverzeichnis; // Schriftverzeichnis der Schriftart

    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var array
     */
    public $oPreisverlaufData_arr; // Daten vom Preisverlauf aus der Datenbank
    public $oConfig_arr; // Daten vom Backend für die Einstellung von Farben, Padding, Größe etc.
    public $oPreisConfig; // Währung und Steuersatz der Preise

    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var string image
     */
    public $image; // Bild vom Graphen

    /**
     * PreisverlaufGraph Members
     *
     * @access public
     * @var string
     */
    public $ColorBackground; // Hintergrundfarbe des Bildes
    public $ColorGrid; // Gridfarbe
    public $ColorGraph; // Graphenfarbe
    public $ColorBox; // Boxfarbe
    public $ColorText; // Textfarbe

    /**
     * @param int    $kArtikel
     * @param int    $kKundegruppe
     * @param int    $nMonat
     * @param array  $oConfig_arr
     * @param object $oPreisConfig
     */
    public function __construct($kArtikel, $kKundegruppe, $nMonat, $oConfig_arr, $oPreisConfig)
    {
        $this->nPolsterLinks   = 25;
        $this->nPolsterRechts  = 25;
        $this->nPolsterOben    = 25;
        $this->nPolsterUnten   = 25;
        $this->nInternPolsterX = 3;
        $this->nInternPolsterY = 3;

        $this->nBreiteRahmen        = 0;
        $this->nHoeheRahmen         = 0;
        $this->nInternPolsterXPixel = 0;
        $this->nInternPolsterYPixel = 0;
        $this->nInnenRahmenBreite   = 0;
        $this->nInnenRahmenHoehe    = 0;
        $this->nAussenRahmenOben    = 0;
        $this->nAussenRahmenLinks   = 0;
        $this->nAussenRahmenUnten   = 0;
        $this->nAussenRahmenRechts  = 0;
        $this->nInnenRahmenOben     = 0;
        $this->nInnenRahmenLinks    = 0;
        $this->nInnenRahmenUnten    = 0;
        $this->nInnenRahmenRechts   = 0;

        $this->nHoehe          = 0;
        $this->nBreite         = 0;
        $this->nAnzahlPreise   = 0;
        $this->nAnzahlTage     = 0;
        $this->nStepX          = 0;
        $this->nStepY          = 0;
        $this->nMaxTimestamp   = 0;
        $this->nMinTimestamp   = 0;
        $this->fMaxPreis       = 0.0;
        $this->fMinPreis       = 0.0;
        $this->fDiffPreis      = 0.0;
        $this->nStep           = 0;
        $this->fStepWert_arr   = [0.25, 0.5, 1.0, 2.5, 5.0, 7.5, 10.0, 12.5, 15.0, 25.0, 50.0, 100.0, 250.0, 2500.0, 25000.0];
        $this->ColorBackground = [255, 255, 255];
        $this->ColorGrid       = [255, 255, 255];
        $this->ColorGraph      = [255, 255, 255];
        $this->ColorBox        = [255, 255, 255];
        $this->ColorText       = [255, 255, 255];
        $this->nSchriftgroesse = 8;
        //$this->cSchriftart = 'arial.ttf';
        //$this->cSchriftverzeichnis = dirname(__FILE__) . '/';
        $this->cSchriftart         = 'GeosansLight.ttf';
        $this->cSchriftverzeichnis = PFAD_ROOT . PFAD_FONTS . '/';

        $this->oConfig_arr  = $oConfig_arr;
        $this->oPreisConfig = $oPreisConfig;
        $this->setzeBreiteHoehe();
        $this->berechneSigniPunkte();
        $this->image = @imagecreate($this->nBreite, $this->nHoehe);
        $this->berechneFarbHexNachDec();
        imagecolorallocate($this->image, $this->ColorBackground[0], $this->ColorBackground[1], $this->ColorBackground[2]);

        if ($this->berechneMinMaxPreis((int)$kArtikel, (int)$kKundegruppe, (int)$nMonat)) {
            $this->berechneYPreisStep();
        }
    }

    /**
     * Holt den Preisverlauf für den aktuellen Artikel aus der Datenbank
     *
     * @param int $kArtikel
     * @param int $kKundegruppe
     * @param int $nMonat
     * @return array
     */
    public function holePreisverlauf($kArtikel, $kKundegruppe, $nMonat)
    {
        $oPreisverlauf_arr = Shop::DB()->query(
            "SELECT fVKNetto, UNIX_TIMESTAMP(dDate) AS timestamp
                FROM tpreisverlauf
                WHERE kArtikel = " . (int)$kArtikel . "
                    AND kKundengruppe = " . (int)$kKundegruppe . "
                    AND DATE_SUB(now(), INTERVAL " . (int)$nMonat . " MONTH) < dDate
                ORDER BY dDate DESC", 2
        );
        if ($oPreisverlauf_arr !== false && count($oPreisverlauf_arr) > 0) {
            $this->nAnzahlTage = count($oPreisverlauf_arr);

            if ($this->oPreisConfig->Netto > 0) {
                foreach ($oPreisverlauf_arr as $i => $oPreisverlauf) {
                    $oPreisverlauf_arr[$i]->fVKNetto += ($oPreisverlauf->fVKNetto * ($this->oPreisConfig->Netto / 100.0));
                }
            }

            if ($this->nAnzahlTage > 1) {
                $this->nMaxTimestamp = $oPreisverlauf_arr[0]->timestamp;
                $this->nMinTimestamp = $oPreisverlauf_arr[count($oPreisverlauf_arr) - 1]->timestamp;
                $this->nDiffStamp    = $this->nMaxTimestamp - $this->nMinTimestamp;

                return $oPreisverlauf_arr;
            }
            if ($this->nAnzahlTage === 1) {
                $this->nMaxTimestamp = $oPreisverlauf_arr[0]->timestamp;
                $this->nMinTimestamp = $this->nMaxTimestamp;

                return $oPreisverlauf_arr;
            }
        }

        return [];
    }

    /**
     * Berechnet für den aktuellen Artikel den maximalen und minimalen Preis
     *
     * @param int $kArtikel
     * @param int $kKundegruppe
     * @param int $nMonat
     * @return bool
     */
    public function berechneMinMaxPreis($kArtikel, $kKundegruppe, $nMonat)
    {
        $this->oPreisverlaufData_arr = $this->holePreisverlauf($kArtikel, $kKundegruppe, $nMonat);

        if (is_array($this->oPreisverlaufData_arr) && count($this->oPreisverlaufData_arr) > 1) {
            $fVKNetto_arr = [];

            foreach ($this->oPreisverlaufData_arr as $oPreisverlauf) {
                $fVKNetto_arr[] = $oPreisverlauf->fVKNetto;
            }

            $this->fMaxPreis  = round((float)max($fVKNetto_arr), 2);
            $this->fMinPreis  = round((float)min($fVKNetto_arr), 2);
            $this->fDiffPreis = $this->fMaxPreis - $this->fMinPreis;
        } elseif ($this->oPreisverlaufData_arr !== null && count($this->oPreisverlaufData_arr) === 1) {
            $this->fMaxPreis = $this->oPreisverlaufData_arr[0]->fVKNetto;
            $this->fMinPreis = $this->fMaxPreis;
        } else {
            return false;
        }

        imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);

        return true;
    }

    /**
     * Berechnet den Y Werteschritt für die Beschriftung
     */
    public function berechneYPreisStep()
    {
        if ($this->nAnzahlTage > 1) {
            if (count($this->fStepWert_arr) > 0) {
                foreach ($this->fStepWert_arr as $i => $fStepWert) {
                    if (($this->fDiffPreis / $fStepWert) < 10) {
                        $this->nStep = $i;
                        break;
                    }
                }
                imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);

                $this->fMaxPreis = round(
                    (
                        (($this->fMaxPreis * 100) - (($this->fMaxPreis * 100) % ($this->fStepWert_arr[$this->nStep] * 100))) +
                        ($this->fStepWert_arr[$this->nStep] * 100)
                    ) / 100,
                    2
                );
                $this->fMinPreis = round(
                    (($this->fMinPreis * 100) - (($this->fMinPreis * 100) % ($this->fStepWert_arr[$this->nStep] * 100)))
                     / 100,
                    2
                );

                $this->fDiffPreis    = $this->fMaxPreis - $this->fMinPreis;
                $this->nAnzahlPreise = (int)($this->fDiffPreis / $this->fStepWert_arr[$this->nStep]);
            }
        } elseif ($this->nAnzahlTage == 1) {
            $this->nAnzahlPreise = 1;
        }
    }

    /**
     * Zeichnet die Aussenbox
     */
    public function zeichneAussenBox()
    {
        $BoxColor = imagecolorallocate($this->image, $this->ColorBox[0], $this->ColorBox[1], $this->ColorBox[2]);

        imageline
        ($this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenOben,
            $BoxColor
        ); // Oben
        imageline(
            $this->image,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Rechts
        imageline(
            $this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Links
        imageline(
            $this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenUnten,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Unten
    }

    /**
     * Zeichnet für den aktuellen Artikel das Grid in die Aussenbox
     */
    public function zeichneGrid()
    {
        // Farben
        $GridColor = imagecolorallocate($this->image, $this->ColorGrid[0], $this->ColorGrid[1], $this->ColorGrid[2]);
        $TextColor = imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);
        //$nTimestampXWert = time();
        $nTimestampXWert = $this->nMaxTimestamp;
        // Y-Achsen Ausrichtung der Beschriftung
        $nBeschriftungsEinzug = ($this->fMaxPreis > 1000)
            ? 75
            : 65;
        if ($this->nAnzahlPreise > 1) {
            // Pixel pro Schritt Y Achse
            $nPixelProSchrittY = $this->nInnenRahmenHoehe / $this->nAnzahlPreise;

            if ($this->nAnzahlTage < 6) {
                $nLoop = $this->nAnzahlTage;
                // Timestampschritt
                $nTimestampXSchritt = $this->nDiffStamp / ($this->nAnzahlTage - 1);
                // Pixel pro Schritt X Achse
                $nPixelProSchrittX = $this->nInnenRahmenBreite / ($this->nAnzahlTage - 1);
            } else {
                $nLoop = 6;
                // Timestampschritt
                $nTimestampXSchritt = $this->nDiffStamp / 6;
                // Pixel pro Schritt X Achse
                $nPixelProSchrittX = $this->nInnenRahmenBreite / 5;
            }
            // Grid X
            imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nInnenRahmenRechts - 15,
                $this->nAussenRahmenUnten + 15,
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                date('j. M', $nTimestampXWert)
            );
            imageline(
                $this->image,
                $this->nInnenRahmenRechts,
                $this->nAussenRahmenOben,
                $this->nInnenRahmenRechts,
                $this->nAussenRahmenUnten,
                $GridColor
            );

            $this->nStepX = $this->nInnenRahmenRechts;

            for ($i = 1; $i < $nLoop; $i++) {
                $this->nStepX -= $nPixelProSchrittX;
                $nTimestampXWert -= $nTimestampXSchritt;

                imagefttext(
                    $this->image,
                    $this->nSchriftgroesse,
                    0,
                    $this->nStepX - 15,
                    $this->nAussenRahmenUnten + 15,
                    $TextColor,
                    $this->cSchriftverzeichnis . $this->cSchriftart,
                    date('j. M', $nTimestampXWert)
                );
                imageline(
                    $this->image,
                    $this->nStepX,
                    $this->nAussenRahmenOben,
                    $this->nStepX,
                    $this->nAussenRahmenUnten,
                    $GridColor
                );
            }

            // Grid Y
            $this->nStepY = $this->nInnenRahmenOben;
            $fPreis       = $this->fMaxPreis;

            imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                $this->nStepY + ($this->nSchriftgroesse / 2),
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                round($fPreis, 2) . ' ' . $this->oPreisConfig->Waehrung
            );
            imageline($this->image,
                $this->nAussenRahmenLinks,
                $this->nStepY,
                $this->nAussenRahmenRechts,
                $this->nStepY,
                $GridColor
            );

            for ($i = 0; $i < $this->nAnzahlPreise; $i++) {
                $this->nStepY += $nPixelProSchrittY;
                $fPreis -= $this->fStepWert_arr[$this->nStep];
                imagefttext(
                    $this->image,
                    $this->nSchriftgroesse,
                    0,
                    $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                    $this->nStepY + ($this->nSchriftgroesse / 2),
                    $TextColor,
                    $this->cSchriftverzeichnis . $this->cSchriftart,
                    round($fPreis, 2) . ' ' . $this->oPreisConfig->Waehrung
                );
                imageline(
                    $this->image,
                    $this->nAussenRahmenLinks,
                    $this->nStepY,
                    $this->nAussenRahmenRechts,
                    $this->nStepY,
                    $GridColor
                );
            }
        } elseif ($this->nAnzahlPreise == 1) {
            // Grid X
            imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                ($this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2) - 15,
                $this->nAussenRahmenUnten + 15,
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                date('j. M', $nTimestampXWert)
            );
            imageline(
                $this->image,
                $this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2,
                $this->nAussenRahmenOben,
                $this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2,
                $this->nAussenRahmenUnten,
                $GridColor
            );
            // Grid Y
            imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                ($this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2) + ($this->nSchriftgroesse / 2),
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                round($this->fMinPreis, 2) . ' ' . $this->oPreisConfig->Waehrung
            );
        }
    }

    /**
     * Zeichnet für einen Artikel den aktuellen Preisverlauf
     */
    public function zeichnePreisverlauf()
    {
        // Preis am letzten X Grid Punkt
        $fXStartPreis = $this->oPreisverlaufData_arr[0]->fVKNetto;
        // X StartWert
        $nXStartWert = $this->nInnenRahmenRechts;
        // Aktueller X Wert
        $nXWertNow = $nXStartWert;
        // Farben
        $GraphColor = imagecolorallocate($this->image, $this->ColorGraph[0], $this->ColorGraph[1], $this->ColorGraph[2]);

        if (is_array($this->oPreisverlaufData_arr) && count($this->oPreisverlaufData_arr) > 1) {
            $nSecProPixel = $this->nDiffStamp / ($nXStartWert - $this->nStepX);
            // Pixel pro X Schritt
            if ($this->nAnzahlTage < 6) {
                $nPixelProX = ($nXStartWert - $this->nStepX) / ($this->nAnzahlTage - 1);
            } else {
                $nPixelProX = ($nXStartWert - $this->nStepX) / 6;
            }
            // X Endwert
            $nXEnd = 0;
            $nYEnd = 0;
            $pvdc  = (count($this->oPreisverlaufData_arr) - 1);
            for ($i = 0; $i < $pvdc; $i++) {
                // Hole Y Wert für den Linienanfang
                $nYWert = $this->holeYPreis($this->oPreisverlaufData_arr[$i]->fVKNetto);
                // Hole Y Wert für den Linienanfang vom nächsten Preis
                $nYWertNext   = $this->holeYPreis($this->oPreisverlaufData_arr[$i + 1]->fVKNetto);
                $nPixelBreite = ($this->oPreisverlaufData_arr[$i]->timestamp - $this->oPreisverlaufData_arr[$i + 1]->timestamp) / $nSecProPixel;
                // Zeichne X Linie
                imageline($this->image, $nXWertNow, $nYWertNext, $nXWertNow - $nPixelBreite, $nYWertNext, $GraphColor);
                // Zeichne Y Linie
                imageline($this->image, $nXWertNow, $nYWert, $nXWertNow, $nYWertNext, $GraphColor);

                $nXEnd = $nXWertNow - $nPixelBreite;
                $nYEnd = $nYWertNext;
                // Aktueller X Wert
                $nXWertNow -= $nPixelBreite;
            }

            // Ränderspitzen
            imageline(
                $this->image,
                $nXStartWert + 5,
                $this->holeYPreis($fXStartPreis),
                $nXStartWert,
                $this->holeYPreis($fXStartPreis),
                $GraphColor
            ); // Rechts
            imageline($this->image, $nXEnd, $nYEnd, $nXEnd - 5, $nYEnd, $GraphColor); // Links
        } elseif (is_array($this->oPreisverlaufData_arr) && count($this->oPreisverlaufData_arr) == 1) {
            imageline(
                $this->image,
                $this->nAussenRahmenLinks,
                $this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2,
                $this->nAussenRahmenRechts,
                $this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2,
                $GraphColor
            );
        }
    }

    /**
     * Berechnet zu jedem Preis aus der Datenbank, den Y Punkt
     *
     * @param $fVKNetto
     * @return int
     */
    public function holeYPreis($fVKNetto)
    {
        $nPixelProCent = ($this->nStepY - $this->nInnenRahmenOben) / (($this->fMaxPreis - $this->fMinPreis) * 100);

        return ($this->nInnenRahmenOben +
                ($this->nStepY - $this->nInnenRahmenOben)) -
            ((($fVKNetto - $this->fMinPreis) * 100) * $nPixelProCent);
    }

    /**
     * Rechnet die gesetzten Hexwerte vom Adminmenü in Dezimalwerte um
     */
    public function berechneFarbHexNachDec()
    {
        if (count($this->oConfig_arr) > 0) {
            foreach ($this->oConfig_arr as $i => $oConfig) {
                if (preg_match("/#[A-Fa-f0-9]{6}/", $oConfig->cWert) == 1) {
                    $nDecZahl_arr = [];

                    $cWertSub       = substr($oConfig->cWert, 1, strlen($oConfig->cWert) - 1);
                    $nDecZahl_arr[] = hexdec(substr($cWertSub, 0, 2));
                    $nDecZahl_arr[] = hexdec(substr($cWertSub, 2, 2));
                    $nDecZahl_arr[] = hexdec(substr($cWertSub, 4, 2));

                    switch ($oConfig->cName) {
                        case 'preisverlauf_hintergrundfarbe':
                            $this->ColorBackground = $nDecZahl_arr;
                            break;
                        case 'preisverlauf_gridfarbe':
                            $this->ColorGrid = $nDecZahl_arr;
                            break;
                        case 'preisverlauf_graphfarbe':
                            $this->ColorGraph = $nDecZahl_arr;
                            break;
                        case 'preisverlauf_boxfarbe':
                            $this->ColorBox = $nDecZahl_arr;
                            break;
                        case 'preisverlauf_textfarbe':
                            $this->ColorText = $nDecZahl_arr;
                            break;
                    }
                }
            }
        }
    }

    /**
     * Breite und Höhe sowie Schriftgröße und Paddings
     */
    public function setzeBreiteHoehe()
    {
        if (count($this->oConfig_arr) > 0) {
            foreach ($this->oConfig_arr as $oConfig) {
                switch ($oConfig->cName) {
                    case 'preisverlauf_breite':
                        $this->nBreite = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_hoehe':
                        $this->nHoehe = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_schriftgroesse':
                        $this->nSchriftgroesse = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_oben':
                        $this->nPolsterOben = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_links':
                        $this->nPolsterLinks = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_unten':
                        $this->nPolsterUnten = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_rechts':
                        $this->nPolsterRechts = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_x':
                        $this->nInternPolsterX = (int)$oConfig->cWert;
                        break;
                    case 'preisverlauf_padding_y':
                        $this->nInternPolsterY = (int)$oConfig->cWert;
                        break;
                }
            }
        }
    }

    /**
     * Berechnet signifikante Schlüsselpunkte um weitere Berechnungen zu erleichtern
     */
    public function berechneSigniPunkte()
    {
        // Breite und Höhe des äusseren Rahmens
        $this->nBreiteRahmen = ($this->nBreite - $this->nPolsterRechts) - $this->nPolsterLinks;
        $this->nHoeheRahmen  = ($this->nHoehe - $this->nPolsterUnten) - $this->nPolsterOben;
        // Padding vom äusseren Rahmen zum Inneren in Pixel anstatt %
        $this->nInternPolsterXPixel = ($this->nBreiteRahmen * ($this->nInternPolsterX / 100));
        $this->nInternPolsterYPixel = ($this->nHoeheRahmen * ($this->nInternPolsterY / 100));
        // Breite und Höhe vom inneren Rahmen
        $this->nInnenRahmenBreite = $this->nBreiteRahmen - (2 * $this->nInternPolsterXPixel);
        $this->nInnenRahmenHoehe  = $this->nHoeheRahmen - (2 * $this->nInternPolsterXPixel);
        // Box AussenBox
        $this->nAussenRahmenOben   = $this->nPolsterOben;
        $this->nAussenRahmenLinks  = $this->nPolsterLinks;
        $this->nAussenRahmenUnten  = $this->nHoehe - $this->nPolsterUnten;
        $this->nAussenRahmenRechts = $this->nBreite - $this->nPolsterRechts;
        // Innen Box
        $this->nInnenRahmenOben   = $this->nPolsterOben + $this->nInternPolsterYPixel;
        $this->nInnenRahmenLinks  = $this->nPolsterLinks + $this->nInternPolsterXPixel;
        $this->nInnenRahmenUnten  = $this->nHoehe + $this->nPolsterUnten - $this->nInternPolsterYPixel;
        $this->nInnenRahmenRechts = $this->nBreite - $this->nPolsterRechts - $this->nInternPolsterXPixel;
    }

    /**
     * Zeichnet den Graphen
     */
    public function zeichneGraphen()
    {
        $this->zeichneGrid();
        $this->zeichnePreisverlauf();
        $this->zeichneAussenBox();

        imagecolorallocate($this->image, $this->ColorBox[0], $this->ColorBox[1], $this->ColorBox[2]);

        header('Content-type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
    }
}
