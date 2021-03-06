<?php

namespace SilverStripe\ControllerPolicy;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;

/**
 * This extension leverages the CachingPolicy's ability to customise the max-age per originator.
 * The configuration option is surfaced to the CMS UI. The extension needs to be added
 * to the object related to the policed controller.
 */
class PageControlledPolicy extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'MaxAge' => 'Varchar'
    ];

    /**
     * Extension point for the CachingPolicy.
     *
     * @param int $cacheAge The original cache age value (in seconds)
     * @return int|null The new cache age value (in seconds)
     */
    public function getCacheAge($cacheAge)
    {
        if ($this->owner->MaxAge != '') {
            return (int)($this->owner->MaxAge * 60);
        }
    }

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        // Only admins are allowed to modify this.
        $member = Security::getCurrentUser();
        if (!$member || !Permission::checkMember($member, 'ADMIN')) {
            return;
        }

        $fields->addFieldsToTab('Root.Caching', [
            LiteralField::create('Instruction', '<p>The following field controls the length of time the page will ' .
                'be cached for. You will not be able to see updates to this page for at most the specified ' .
                'amount of minutes. Leave empty to set back to the default configured for your site. Set ' .
                'to 0 to explicitly disable caching for this page.</p>'),
            TextField::create('MaxAge', 'Custom cache timeout [minutes]')
        ]);
    }
}
