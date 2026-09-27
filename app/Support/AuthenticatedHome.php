<?php

namespace App\Support;

use App\Models\User;

class AuthenticatedHome
{
    public static function routeName(User $user): string
    {
        if ($user->can('dashboard.view')) {
            return 'dashboard';
        }

        foreach ([
            'crm.submissions.view' => 'crm.submissions.index',
            'crm.referrals.view' => 'crm.student-referrals.index',
            'crm.exhibitions.view' => 'crm.exhibition.index',
            'crm.event_contacts.view' => 'crm.event-contacts.index',
            'crm.partners.view' => 'crm.partners.index',
        ] as $permission => $route) {
            if ($user->can($permission)) {
                return $route;
            }
        }

        return 'profile.edit';
    }
}
