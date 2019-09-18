<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Pagination
 */
class Pagination
{
    /**
     * @var string
     */
    private $cId = 'pagi';

    /**
     * @var int
     */
    private $nDispPagesRadius = 2;

    /**
     * @var array
     */
    private $nItemsPerPageOption_arr = [10, 20, 50, 100];

    /**
     * @var array
     */
    private $cSortByOption_arr = [];

    /**
     * @var int
     */
    private $nItemCount = 0;

    /**
     * @var int
     */
    private $nItemsPerPage = 10;

    /**
     * @var bool
     */
    private $bItemsPerPageExplicit = false;

    /**
     * @var int
     */
    private $nSortBy = 0;

    /**
     * @var int
     */
    private $nSortDir = 0;

    /**
     * @var int
     */
    private $nSortByDir = 0;

    /**
     * @var int
     */
    private $nPage = 0;

    /**
     * @var int
     */
    private $nPageCount = 0;

    /**
     * @var int
     */
    private $nPrevPage = 0;

    /**
     * @var int
     */
    private $nNextPage = 0;

    /**
     * @var int
     */
    private $nLeftRangePage = 0;

    /**
     * @var int
     */
    private $nRightRangePage = 0;

    /**
     * @var int
     */
    private $nFirstPageItem = 0;

    /**
     * @var int
     */
    private $nPageItemCount = 0;

    /**
     * @var string
     */
    private $cSortBy = '';

    /**
     * @var string
     */
    private $cSortDir = '';

    /**
     * @var string
     */
    private $cLimitSQL = '';

    /**
     * @var string
     */
    private $cOrderSQL = '';

    /**
     * @var array
     */
    private $oItem_arr;

    /**
     * @var array
     */
    private $oPageItem_arr;

    /**
     * @var int
     */
    private $nDefaultItemsPerPage = 0;

    /**
     * Pagination constructor.
     * @param string $cId
     */
    public function __construct($cId = null)
    {
        if (is_string($cId)) {
            $this->cId = $cId;
        }
    }

    /**
     * @param string $cId - page-unique name for this pagination
     * @return $this
     */
    public function setId($cId)
    {
        $this->cId = $cId;

        return $this;
    }

    /**
     * @param int $nRange - number of page buttons to be displayed before and after the active page button
     * @return $this
     */
    public function setRange($nRange)
    {
        $this->nDispPagesRadius = $nRange;

        return $this;
    }

    /**
     * @param $nItemsPerPageOption_arr - array of integers to be offered as items per page count options (non-empty)
     * @return $this
     */
    public function setItemsPerPageOptions($nItemsPerPageOption_arr)
    {
        $this->nItemsPerPageOption_arr = $nItemsPerPageOption_arr;

        return $this;
    }

    /**
     * @param array $cSortByOption_arr - array of [$cColumnName, $cDisplayTitle] pairs to be offered as sorting options
     * @return $this
     */
    public function setSortByOptions($cSortByOption_arr)
    {
        $this->cSortByOption_arr = $cSortByOption_arr;

        return $this;
    }

    /**
     * @param int $nItemCount - number of items to be paginated
     * @return $this
     */
    public function setItemCount($nItemCount)
    {
        $this->nItemCount = (int)$nItemCount;

        return $this;
    }

    /**
     * @param $oItem_arr - item array to be paginated and sorted
     * @return $this
     */
    public function setItemArray($oItem_arr)
    {
        $this->oItem_arr = $oItem_arr;
        $this->setItemCount(count($oItem_arr));

        return $this;
    }

    /**
     * @param int $n - -1 means: all items / 0 means: use first option of $nItemsPerPageOption_arr
     * @return $this
     */
    public function setDefaultItemsPerPage($n)
    {
        $this->nDefaultItemsPerPage = (int)$n;

        return $this;
    }

    /**
     * Explicitly set the number of items per page. This overrides any custom selection.
     *
     * @param int $nItemsPerPage
     * @return $this
     */
    public function setItemsPerPage($nItemsPerPage)
    {
        $this->bItemsPerPageExplicit = true;
        $this->nItemsPerPage         = $nItemsPerPage;

        return $this;
    }

    /**
     * Load parameters from GET, POST or SESSION store
     * @return $this
     */
    public function loadParameters()
    {
        $this->nItemsPerPage =
            $this->bItemsPerPageExplicit                    ? $this->nItemsPerPage : (
            isset($_GET[$this->cId . '_nItemsPerPage'])     ? (int)$_GET[$this->cId . '_nItemsPerPage'] : (
            isset($_POST[$this->cId . '_nItemsPerPage'])    ? (int)$_POST[$this->cId . '_nItemsPerPage'] : (
            isset($_SESSION[$this->cId . '_nItemsPerPage']) ? (int)$_SESSION[$this->cId . '_nItemsPerPage'] : (
            $this->nDefaultItemsPerPage >= -1               ? $this->nDefaultItemsPerPage :
                                                              $this->nItemsPerPageOption_arr[0] ))));

        $this->nSortByDir =
            isset($_GET[$this->cId . '_nSortByDir'])     ? (int)$_GET[$this->cId . '_nSortByDir'] : (
            isset($_POST[$this->cId . '_nSortByDir'])    ? (int)$_POST[$this->cId . '_nSortByDir'] : (
            isset($_SESSION[$this->cId . '_nSortByDir']) ? (int)$_SESSION[$this->cId . '_nSortByDir'] :
                0 ));

        $this->nPage =
            isset($_GET[$this->cId . '_nPage'])     ? (int)$_GET[$this->cId . '_nPage'] : (
            isset($_POST[$this->cId . '_nPage'])    ? (int)$_POST[$this->cId . '_nPage'] : (
            isset($_SESSION[$this->cId . '_nPage']) ? (int)$_SESSION[$this->cId . '_nPage'] :
                                                      0 ));

        return $this;
    }

    /**
     * Assemble the pagination. Create SQL LIMIT and ORDER BY clauses. Sort and slice item array if present
     * @return $this
     */
    public function assemble()
    {
        $this->loadParameters()
             ->storeParameters();

        if ($this->nItemsPerPage === -1) {
            // Show all entries on a single page
            $this->nPageCount      = 1;
            $this->nPage           = 0;
            $this->nPrevPage       = 0;
            $this->nNextPage       = 0;
            $this->nLeftRangePage  = 0;
            $this->nRightRangePage = 0;
            $this->nFirstPageItem  = 0;
            $this->nPageItemCount  = $this->nItemCount;
        } elseif ($this->nItemsPerPage === 0) {
            // Set $nItemsPerPage to default if greater 0 or else to the first option in $nItemsPerPageOption_arr
            $nItemsPerPage         = $this->nDefaultItemsPerPage > 0 ? $this->nDefaultItemsPerPage : $this->nItemsPerPageOption_arr[0];
            $this->nPageCount      = $nItemsPerPage > 0 ? (int)ceil($this->nItemCount / $nItemsPerPage) : 1;
            $this->nPage           = max(0, min($this->nPageCount - 1, $this->nPage));
            $this->nPrevPage       = max(0, min($this->nPageCount - 1, $this->nPage - 1));
            $this->nNextPage       = max(0, min($this->nPageCount - 1, $this->nPage + 1));
            $this->nLeftRangePage  = max(0, $this->nPage - $this->nDispPagesRadius);
            $this->nRightRangePage = min($this->nPageCount - 1, $this->nPage + $this->nDispPagesRadius);
            $this->nFirstPageItem  = $this->nPage * $nItemsPerPage;
            $this->nPageItemCount  = min($nItemsPerPage, $this->nItemCount - $this->nFirstPageItem);
        } else {
            $this->nPageCount      = $this->nItemsPerPage > 0 ? (int)ceil($this->nItemCount / $this->nItemsPerPage) : 1;
            $this->nPage           = max(0, min($this->nPageCount - 1, $this->nPage));
            $this->nPrevPage       = max(0, min($this->nPageCount - 1, $this->nPage - 1));
            $this->nNextPage       = max(0, min($this->nPageCount - 1, $this->nPage + 1));
            $this->nLeftRangePage  = max(0, $this->nPage - $this->nDispPagesRadius);
            $this->nRightRangePage = min($this->nPageCount - 1, $this->nPage + $this->nDispPagesRadius);
            $this->nFirstPageItem  = $this->nPage * $this->nItemsPerPage;
            $this->nPageItemCount  = min($this->nItemsPerPage, $this->nItemCount - $this->nFirstPageItem);
        }

        $this->nSortBy  = (int)($this->nSortByDir / 2);
        $this->nSortDir = $this->nSortByDir % 2;

        if (isset($this->cSortByOption_arr[$this->nSortBy])) {
            // Create ORDER SQL clauses
            $this->cSortBy   = $this->cSortByOption_arr[$this->nSortBy][0];
            $this->cSortDir  = $this->nSortDir === 0 ? 'ASC' : 'DESC';
            $this->cOrderSQL = $this->cSortBy . ' ' . $this->cSortDir;
            $nSortFac        = $this->nSortDir === 0 ? +1 : -1;
            $cSortBy         = $this->cSortBy;

            // Sort array if exists
            if (is_array($this->oItem_arr)) {
                usort($this->oItem_arr, function ($a, $b) use ($cSortBy, $nSortFac) {
                    $valueA = is_string($a->$cSortBy) ? strtolower($a->$cSortBy) : $a->$cSortBy;
                    $valueB = is_string($b->$cSortBy) ? strtolower($b->$cSortBy) : $b->$cSortBy;

                    return $valueA == $valueB ? 0 : ($valueA < $valueB ? -$nSortFac : +$nSortFac);
                });
            }
        }

        $this->cLimitSQL = $this->nFirstPageItem . ',' . $this->nPageItemCount;

        // Slice array if exists
        if (is_array($this->oItem_arr)) {
            $this->oPageItem_arr = array_slice($this->oItem_arr, $this->nFirstPageItem, $this->nPageItemCount);
        }

        return $this;
    }

    /**
     * Store the custom parameters back into the SESSION store
     * @return $this
     */
    public function storeParameters()
    {
        $_SESSION[$this->cId . '_nItemsPerPage'] = $this->nItemsPerPage;
        $_SESSION[$this->cId . '_nSortByDir']    = $this->nSortByDir;
        $_SESSION[$this->cId . '_nPage']         = $this->nPage;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->cId;
    }

    /**
     * @return array
     */
    public function getItemsPerPageOptions()
    {
        return $this->nItemsPerPageOption_arr;
    }

    /**
     * @return array
     */
    public function getSortByOptions()
    {
        return $this->cSortByOption_arr;
    }

    /**
     * @return string
     */
    public function getLimitSQL()
    {
        return $this->cLimitSQL;
    }

    /**
     * @return string
     */
    public function getOrderSQL()
    {
        return $this->cOrderSQL;
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->nItemCount;
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->nItemsPerPage;
    }

    /**
     * @return int
     */
    public function getSortBy()
    {
        return $this->nSortBy;
    }

    /**
     * @return int
     */
    public function getSortDir()
    {
        return $this->nSortDir;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->nPage;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        return $this->nPageCount;
    }

    /**
     * @return int
     */
    public function getPrevPage()
    {
        return $this->nPrevPage;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->nNextPage;
    }

    /**
     * @return int
     */
    public function getLeftRangePage()
    {
        return $this->nLeftRangePage;
    }

    /**
     * @return int
     */
    public function getRightRangePage()
    {
        return $this->nRightRangePage;
    }

    /**
     * @return int
     */
    public function getFirstPageItem()
    {
        return $this->nFirstPageItem;
    }

    /**
     * @return int
     */
    public function getPageItemCount()
    {
        return $this->nPageItemCount;
    }

    /**
     * @return array
     */
    public function getPageItems()
    {
        return $this->oPageItem_arr;
    }

    /**
     * @return string - 'ASC' or 'DESC'
     */
    public function getSortDirSpecifier()
    {
        return $this->cSortDir;
    }

    /**
     * @return string - the column name to sort by
     */
    public function getSortByCol()
    {
        return $this->cSortBy;
    }

    /**
     * @param int $nIndex
     * @return mixed
     */
    public function getItemsPerPageOption($nIndex)
    {
        return $this->nItemsPerPageOption_arr[$nIndex];
    }

    /**
     * @return int
     */
    public function getSortByDir()
    {
        return $this->nSortByDir;
    }
}
