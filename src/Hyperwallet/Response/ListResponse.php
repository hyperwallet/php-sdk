<?php
namespace Hyperwallet\Response;

/**
 * Represents a API list response
 *
 * @package Hyperwallet\Response
 */
class ListResponse implements \Countable, \ArrayAccess {

    /**
     * Total number of matching objects
     *
     * @var int
     */
    private $count;

    /**
     * Array of Model's
     *
     * @var array
     */
    private $data;

    /**
     * Creates a api list response instance
     *
     * @param array $body The api response body
     * @param callable $convertEntry
     */
    public function __construct(array $body, $convertEntry) {
        if (count($body) == 0) {
            $this->count = 0;
            $this->data = array();
        } else {
            $this->count = $body['count'];
            $this->data = array_map(function ($item) use ($convertEntry) {
                if (isset($item['links'])) {
                    unset($item['links']);
                }
                return $convertEntry($item);
            }, $body['data']);
        }
    }

    /**
     * Get the total number of matching objects
     *
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Get the array of Model's
     *
     * @return array
     */
    public function getData() {
        return $this->data;
    }


    /**
     * @internal
     *
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() {
        return count($this->data);
    }

    /**
     * @internal
     *
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    /**
     * @internal
     *
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    /**
     * @internal
     *
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    /**
     * @internal
     * 
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
}
