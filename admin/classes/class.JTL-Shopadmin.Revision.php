<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ifndef('MAX_REVISIONS', 5);

/**
 * Class Revision
 */
class Revision
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * Revision constructor.
     */
    public function __construct()
    {
        $this->mapping = [
            'link'   => [
                'table'         => 'tlink',
                'id'            => 'kLink',
                'reference'     => 'tlinksprache',
                'reference_id'  => 'kLink',
                'reference_key' => 'cISOSprache'
            ],
            'export' => [
                'table' => 'texportformat',
                'id'    => 'kExportformat'
            ],
            'mail'   => [
                'table'         => 'temailvorlage',
                'id'            => 'kEmailvorlage',
                'reference'     => 'temailvorlagesprache',
                'reference_id'  => 'kEmailvorlage',
                'reference_key' => 'kSprache'
            ],
            'news' => [
                'table' => 'tnews',
                'id'    => 'kNews'
            ]
        ];
    }

    /**
     * @param string $type
     * @return string|null
     */
    private function getMapping($type)
    {
        return isset($this->mapping[$type])
            ? $this->mapping[$type]
            : null;
    }

    /**
     * @param string $name
     * @param array  $mapping
     * @return $this
     */
    public function addMapping($name, $mapping)
    {
        $this->mapping[$name] = $mapping;

        return $this;
    }

    /**
     * @param int $id
     * @return object
     */
    public function getRevision($id)
    {
        return Shop::DB()->select('trevisions', 'id', (int)$id);
    }

    /**
     * @param string      $type
     * @param int         $key
     * @param bool        $secondary
     * @param null|string $author
     * @param bool        $utf8
     * @return bool
     * @throws InvalidArgumentException
     */
    public function addRevision($type, $key, $secondary = false, $author = null, $utf8 = true)
    {
        if (MAX_REVISIONS <= 0) {
            return false;
        }
        $key = (int)$key;
        if (!empty($key) && ($mapping = $this->getMapping($type)) !== null) {
            if ($author === null) {
                $author = isset($_SESSION['AdminAccount']->cLogin)
                    ? $_SESSION['AdminAccount']->cLogin
                    : '?';
            }
            $field           = $mapping['id'];
            $currentRevision = Shop::DB()->select($mapping['table'], $mapping['id'], $key);
            if (empty($currentRevision->$field)) {
                return false;
            }
            $revision                     = new stdClass();
            $revision->type               = $type;
            $revision->reference_primary  = $key;
            $revision->content            = $currentRevision;
            $revision->author             = $author;
            $revision->custom_table       = $mapping['table'];
            $revision->custom_primary_key = $mapping['id'];

            if ($secondary !== false && !empty($mapping['reference'])) {
                $field               = $mapping['reference_key'];
                $referencedRevisions = Shop::DB()->selectAll($mapping['reference'], $mapping['reference_id'], $key);
                if (empty($referencedRevisions)) {
                    return false;
                }
                $revision->content->references = [];
                foreach ($referencedRevisions as $referencedRevision) {
                    $revision->content->references[$referencedRevision->$field] = $referencedRevision;
                }
            }
            if ($utf8 === true) {
                $revision->content = utf8_convert_recursive($revision->content);
            }
            $revision->content = json_encode($revision->content);
            $this->storeRevision($revision);
            $this->housekeeping($type, $key);

            return true;
        }

        throw new InvalidArgumentException('Invalid type/key given. Got type ' . $type . ' and key ' . $key);
    }

    /**
     * @param string $type
     * @param int    $primary
     * @return array|int
     */
    public function getRevisions($type, $primary)
    {
        $revisions = Shop::DB()->selectAll('trevisions', ['type', 'reference_primary'], [$type, $primary], '*', 'timestamp DESC');
        foreach ($revisions as $revision) {
            $revision->content = json_decode($revision->content);
        }

        return $revisions;
    }

    /**
     * @return $this
     */
    public function deleteAll()
    {
        Shop::DB()->query('TRUNCATE table trevisions', 3);

        return $this;
    }

    /**
     * @param object $revision
     * @return int
     */
    private function storeRevision($revision)
    {
        return Shop::DB()->insert('trevisions', $revision);
    }

    /**
     * @param string $type
     * @param int    $id
     * @param bool   $secondary
     * @param bool   $utf8
     * @return bool
     */
    public function restoreRevision($type, $id, $secondary = false, $utf8 = true)
    {
        $revision = $this->getRevision($id);
        $mapping  = $this->getMapping($type); //get static mapping from build in content types
        if ($mapping === null && !empty($revision->custom_table) && !empty($revision->custom_primary_key)) {
            //load dynamic mapping from DB
            $mapping = ['table' => $revision->custom_table, 'id' => $revision->custom_primary_key];
        }
        if (isset($revision->id) && $mapping !== null) {
            $oldCOntent = json_decode($revision->content);
            $primaryRow = $mapping['id'];
            $primaryKey = $oldCOntent->$primaryRow;
            unset($oldCOntent->$primaryRow);
            if ($utf8 === true) {
                $oldCOntent = utf8_convert_recursive($oldCOntent, false);
            }
            if ($secondary === false) {
                return Shop::DB()->update($mapping['table'], $primaryRow, $primaryKey, $oldCOntent) === 1;
            }
            if ($secondary === true && isset($mapping['reference_key'], $oldCOntent->references)) {
                $tableToUpdate = $mapping['reference'];
                $secondaryRow  = $mapping['reference_key']; //most likely something like "kSprache"
                foreach ($oldCOntent->references as $key => $value) {
                    //$key is the index in the reference array - which corresponds to the foreign key
                    unset($value->$primaryRow, $value->$secondaryRow);
                    if ($utf8 === true) {
                        $value = utf8_convert_recursive($value, false);
                    }
                    Shop::DB()->update($tableToUpdate, [$primaryRow, $secondaryRow], [$primaryKey, $key], $value);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * delete single revision
     *
     * @param int $id
     * @return int
     */
    public function deleteRevision($id)
    {
        return Shop::DB()->delete('trevisions', 'id', (int)$id);
    }

    /**
     * remove revisions that would add up to more then MAX_REVISIONS
     *
     * @param string $type
     * @param int    $key
     * @return int
     */
    private function housekeeping($type, $key)
    {
        return Shop::DB()->query("
            DELETE a 
              FROM trevisions AS a 
                JOIN
                    ( 
                      SELECT id 
                        FROM trevisions 
                        WHERE type = '" . $type . "' 
                            AND reference_primary = " . $key . " 
                        ORDER BY timestamp DESC 
                        LIMIT 99999 OFFSET " . MAX_REVISIONS . "
                    ) AS b
                    ON a.id = b.id", 3
        );
    }
}
