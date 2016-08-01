<?php
return array(
    'avd1' => array(
        'api_visibility_domain_id' => 1,
        'api_id' => 17,
        'domain' => 'invited-domain.example.com',
        'invited_by_user_id' => 17,
        'created' => '2016-06-07 14:18:40',
        'updated' => '2016-06-07 14:18:40',
    ),
    'avd2' => array(
        'api_visibility_domain_id' => 2,
        'api_id' => 21,
        'domain' => 'invited-domain.example.com',
        'invited_by_user_id' => 17,
        'created' => '2016-07-20 11:38:00',
        'updated' => '2016-07-20 11:38:00',
    ),
    'avdWithTwoDependentKeys' => array(
        'api_visibility_domain_id' => 3,
        'api_id' => 17,
        'domain' => '1469472196.example.com',
        'invited_by_user_id' => 17,
        'created' => '2016-07-25 14:43:35',
        'updated' => '2016-07-25 14:43:35',
    ),
    'firstAvdAllowingKeyAllowedByTwoAvds' => array(
        'api_visibility_domain_id' => 4,
        'api_id' => 17,
        'domain' => 'two-avds.example.com',
        'invited_by_user_id' => 17,
        'created' => '2016-07-27 15:07:11',
        'updated' => '2016-07-27 15:07:11',
    ),
    'secondAvdAllowingKeyAllowedByTwoAvds' => array(
        'api_visibility_domain_id' => 5,
        'api_id' => 17,
        'domain' => 'two-avds.example.com',
        'invited_by_user_id' => 17,
        'created' => '2016-07-27 15:07:12',
        'updated' => '2016-07-27 15:07:12',
    ),
);
