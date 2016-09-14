<?php
/**
 * 4-Tell Product Recommendations
 * Copyright © 2015 4-Tell, Inc. All rights reserved.
 */

namespace FourTell\Recommend\Api;

interface FeedInterface
{

    /**
     * Return products
     *
     * @api
     * @return array
     */
    public function getCatalog();

    /**
     * Return products inventory
     *
     * @api
     * @return array
     */
    public function getInventory();

    /**
     *
     * Get the category data
     *
     * @api
     * @return array
     */
    public function getCategoryNames();

    /**
     *
     * Get the Sales data
     *
     * @api
     * @return array
     */
    function getSales();

    /**
     *
     * Get the Returns data
     *
     * @api
     * @return array
     */
    function getReturns();

    /**
     * Get the Customers data
     *
     * @api
     * @return array
     */
    public function getCustomers();

    /**
     *
     * Get the Manufacturers data
     *
     * @api
     * @return array
     */
    function getManufacturerNames();

    /**
     * Get the Version data
     *
     * @api
     * @return array
     */
    public function getVersion();
}