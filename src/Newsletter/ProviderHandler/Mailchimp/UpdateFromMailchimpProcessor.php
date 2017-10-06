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

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;

use CustomerManagementFrameworkBundle\Model\MailchimpAwareCustomerInterface;
use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Mailchimp;
use Pimcore\Model\User;
use Psr\Log\LoggerInterface;

class UpdateFromMailchimpProcessor
{
    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $resolver = \Pimcore::getContainer()->get('pimcore_admin.security.token_storage_user_resolver');
        $this->user = $resolver->getUser();
    }

    /**
     * Updates the customer instance with the given mailchimpStatus.
     * This updates the instance only but applies no save of the customer!
     * Returns true if any status changed during the method.
     *
     * @param Mailchimp $mailchimpHandler
     * @param MailchimpAwareCustomerInterface $mailchimpAwareCustomer
     * @param $status
     *
     * @return bool
     */
    public function updateNewsletterStatus(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, $mailchimpStatus)
    {
        $changed = false;

        if ($newsletterStatus = $mailchimpHandler->reverseMapNewsletterStatus($mailchimpStatus)) {
            if (!$changed && ($mailchimpHandler->getNewsletterStatus($customer) != $newsletterStatus)) {
                $changed = true;
            }
            $mailchimpHandler->setNewsletterStatus($customer, $newsletterStatus);
        }
        if ($mailchimpHandler->getMailchimpStatus($customer) != $mailchimpStatus) {
            $mailchimpHandler->updateMailchimpStatus($customer, $mailchimpStatus, false);
            $changed = true;
        }

        return $changed;
    }

    /**
     * @param Mailchimp $mailchimpHandler
     * @param MailchimpAwareCustomerInterface $customer
     * @param array $mergeFieldData
     * @param LoggerInterface $logger
     *
     * @return bool
     */
    public function processMergeFields(Mailchimp $mailchimpHandler, MailchimpAwareCustomerInterface $customer, array $mergeFieldData)
    {
        $changed = false;
        foreach ($mergeFieldData as $key => $value) {
            if ($reverseMapped = $mailchimpHandler->reverseMapMergeField($key, $value)) {
                $setter = 'set' . ucfirst($reverseMapped['field']);
                $getter = 'get' . ucfirst($reverseMapped['field']);

                $currentPimcoreData = $customer->$getter();

                if ($mailchimpHandler->didMergeFieldDataChange($reverseMapped['field'], $currentPimcoreData, $value)) {
                    $changed = true;
                    $customer->$setter($reverseMapped['value']);
                }
            }
        }

        return $changed;
    }

    public function saveCustomerIfChanged(MailchimpAwareCustomerInterface $customer, $changed)
    {
        if ($changed) {
            if ($this->user) {
                $customer->setUserModification($this->user->getId());
            }

            $customer->saveWithOptions(
                $customer->getSaveManager()->getSaveOptions(true)
                    ->disableValidator()
                    ->disableNewsletterQueue()
            );
        }
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}
