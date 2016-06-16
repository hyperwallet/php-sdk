<?php
namespace Hyperwallet\Model;

/**
 * Interface to mark a resource to contain a program token
 *
 * @package Hyperwallet\Model
 */
interface IProgramAware {

    /**
     * Set the program token
     *
     * @param string $programToken
     * @return mixed
     */
    public function setProgramToken($programToken);

    /**
     * Get the program token
     *
     * @return string
     */
    public function getProgramToken();

}
