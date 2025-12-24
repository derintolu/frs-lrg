<?php
/**
 * FluentCRM Integration for Partnerships
 *
 * @package LendingResourceHub\Integrations
 */

namespace LendingResourceHub\Integrations;

use LendingResourceHub\Traits\Base;

/**
 * FluentCRM Sync Integration for Partnerships
 * Syncs partnership creation/updates to FluentCRM by adding tags
 */
class FluentCRMSync {
    use Base;

    /**
     * Initialize hooks
     */
    public function init(): void {
        // Partnership hooks
        add_action('frs_partnership_created', [$this, 'sync_partnership_created'], 10, 1);
        add_action('frs_partnership_updated', [$this, 'sync_partnership_updated'], 10, 1);
    }

    /**
     * Sync partnership creation to FluentCRM
     * Adds "Active Partnership" tags to both parties
     *
     * @param array $partnership_data Partnership data
     */
    public function sync_partnership_created(array $partnership_data): void {
        if (!function_exists('FluentCrmApi')) {
            return;
        }

        // Add tags to loan officer
        if (!empty($partnership_data['loan_officer_id'])) {
            $this->add_partnership_tags($partnership_data['loan_officer_id'], ['Active Partnership', 'Loan Officer Network']);
        }

        // Add tags to realtor
        if (!empty($partnership_data['realtor_id'])) {
            $this->add_partnership_tags($partnership_data['realtor_id'], ['Active Partnership', 'Realtor Network']);
        }

        error_log(sprintf(
            'FRS LRG: Partnership synced to FluentCRM - LO: %d, Realtor: %d',
            $partnership_data['loan_officer_id'] ?? 0,
            $partnership_data['realtor_id'] ?? 0
        ));
    }

    /**
     * Sync partnership update to FluentCRM
     * Updates tags based on partnership status
     *
     * @param array $partnership_data Partnership data
     */
    public function sync_partnership_updated(array $partnership_data): void {
        if (!function_exists('FluentCrmApi')) {
            return;
        }

        $status = $partnership_data['status'] ?? 'active';

        // If partnership is inactive/terminated, remove active tags
        if ($status !== 'active') {
            if (!empty($partnership_data['loan_officer_id'])) {
                $this->remove_partnership_tags($partnership_data['loan_officer_id'], ['Active Partnership']);
            }

            if (!empty($partnership_data['realtor_id'])) {
                $this->remove_partnership_tags($partnership_data['realtor_id'], ['Active Partnership']);
            }

            error_log(sprintf(
                'FRS LRG: Partnership marked as %s - removed Active Partnership tags',
                $status
            ));
        } else {
            // Re-add tags if partnership reactivated
            $this->sync_partnership_created($partnership_data);
        }
    }

    /**
     * Add tags to FluentCRM contact
     *
     * @param int   $user_id User ID
     * @param array $tags    Tag names to add
     */
    private function add_partnership_tags(int $user_id, array $tags): void {
        try {
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                return;
            }

            $api = FluentCrmApi('contacts');
            $contact = $api->getContactByUserRef($user_id);

            if (!$contact) {
                // Contact doesn't exist, skip
                error_log("FRS LRG: Contact not found in FluentCRM for user #{$user_id}");
                return;
            }

            // Add tags
            $contact->attachTags($tags);

            error_log(sprintf(
                'FRS LRG: Added tags to FluentCRM contact #%d (user #%d): %s',
                $contact->id,
                $user_id,
                implode(', ', $tags)
            ));

        } catch (\Exception $e) {
            error_log(sprintf(
                'FRS LRG: Failed to add tags to FluentCRM for user #%d: %s',
                $user_id,
                $e->getMessage()
            ));
        }
    }

    /**
     * Remove tags from FluentCRM contact
     *
     * @param int   $user_id User ID
     * @param array $tags    Tag names to remove
     */
    private function remove_partnership_tags(int $user_id, array $tags): void {
        try {
            $user = get_user_by('ID', $user_id);
            if (!$user) {
                return;
            }

            $api = FluentCrmApi('contacts');
            $contact = $api->getContactByUserRef($user_id);

            if (!$contact) {
                return;
            }

            // Remove tags
            $contact->detachTags($tags);

            error_log(sprintf(
                'FRS LRG: Removed tags from FluentCRM contact #%d (user #%d): %s',
                $contact->id,
                $user_id,
                implode(', ', $tags)
            ));

        } catch (\Exception $e) {
            error_log(sprintf(
                'FRS LRG: Failed to remove tags from FluentCRM for user #%d: %s',
                $user_id,
                $e->getMessage()
            ));
        }
    }
}
