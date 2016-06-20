<?php
namespace Hyperwallet\Response;

use Hyperwallet\Model\Error;

/**
 * Represents a API error response
 *
 * @package Hyperwallet\Response
 */
class ErrorResponse implements \Countable, \ArrayAccess {

    /**
     * The http status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * The list of errors
     *
     * @var Error[]
     */
    private $errors;

    /**
     * Creates a ErrorResponse instance
     *
     * @param int $statusCode The http status code
     * @param array $errors the errors response body
     */
    public function __construct($statusCode, array $errors) {
        $this->statusCode = $statusCode;
        $this->errors = array_map(function ($error) {
            return new Error($error);
        }, $errors['errors']);
    }

    /**
     * Get the http status code
     *
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * Get the list of errors
     *
     * @return Error[]
     */
    public function getErrors() {
        return $this->errors;
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
        return count($this->errors);
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
        return isset($this->errors[$offset]);
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
        return $this->errors[$offset];
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
        $this->errors[$offset] = $value;
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
        unset($this->errors[$offset]);
    }

}