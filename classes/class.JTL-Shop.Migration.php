<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Migration
 */
class Migration implements JsonSerializable
{
    use MigrationTrait,
        MigrationTableTrait;

    /**
     * @var string
     */
    protected $info;

    /**
     * @var DateTime
     */
    protected $executed;

    /**
     * Migration constructor.
     *
     * @param null|string   $info
     * @param DateTime|null $executed
     */
    public function __construct($info = null, DateTime $executed = null)
    {
        $this->info     = ucfirst(strtolower($info));
        $this->executed = $executed;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return MigrationHelper::mapClassNameToId($this->getName());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * @return null
     */
    public function getAuthor()
    {
        return (isset($this->author) && $this->author !== null)
            ? $this->author : null;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return (isset($this->description) && $this->description !== null)
            ? $this->description : $this->info;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return DateTime::createFromFormat('YmdHis', $this->getId());
    }

    /**
     * @return DateTime
     */
    public function getExecuted()
    {
        return $this->executed;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'author'      => $this->getAuthor(),
            'description' => $this->getDescription(),
            'executed'    => $this->getExecuted(),
            'created'     => $this->getCreated()
        ];
    }
}
