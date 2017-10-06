<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace CustomerManagementFrameworkBundle\CustomerList\Exporter;

use Pimcore\Model\DataObject\Customer;

interface ExporterInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return array
     */
    public function getProperties();

    /**
     * @param array $properties
     */
    public function setProperties(array $properties);

    /**
     * @return Customer\Listing
     */
    public function getListing();

    /**
     * @param Customer\Listing $listing
     */
    public function setListing(Customer\Listing $listing);

    /**
     * Get file MIME type
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Get rendered file size
     *
     * @return int
     */
    public function getFilesize();

    /**
     * Get extension of exported file
     *
     * @return string
     */
    public function getExtension();

    /**
     * Get export data
     *
     * @return []
     */
    public function getExportData();

    /**
     * Generates the export file from given export data.
     *
     * @param array $exportData
     *
     * @return mixed
     */
    public function generateExportFile(array $exportData);
}
