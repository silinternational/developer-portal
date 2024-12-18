<?php

use Sil\DevPortal\models\Api;

return array(
    'api1'  => array(
        'api_id'          => 1,
        'code'            => 'test-auto',
        'display_name'    => 'Api that has auto approval',
        'endpoint'        => 'www.auto.com',
        'queries_second'  => 1,
        'queries_day'     => 1111,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'api2' => array(
        'api_id'          => 2,
        'code'            => 'test-owner',
        'display_name'    => 'Api that has to be approved by the owner',
        'endpoint'        => 'www.owner.com',
        'queries_second'  => 2,
        'queries_day'     => 2222,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'owner_id'        => 17,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'api3'  => array(
        'api_id'          => 3,
        'code'            => 'test-admin',
        'display_name'    => 'Api that has to be approved by an admin',
        'endpoint'        => 'localhost',
        'default_path'    => '/admin',
        'queries_second'  => 3,
        'queries_day'     => 3000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'api4'  => array(
        'api_id'          => 4,
        'code'            => 'test-api4',
        'display_name'    => 'Api Number 4',
        'endpoint'        => 'localhost',
        'default_path'    => '/api4',
        'queries_second'  => 4,
        'queries_day'     => 4000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithOwner'  => array(
        'api_id'          => 5,
        'code'            => 'test-with-owner',
        'display_name'    => 'API With An Owner',
        'endpoint'        => 'localhost',
        'default_path'    => '/5',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'owner_id'        => 8,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'publicApi'  => array(
        'api_id'          => 6,
        'code'            => 'test-public-api',
        'display_name'    => 'A Public API',
        'endpoint'        => 'localhost',
        'default_path'    => '/public',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithTwoKeys'  => array(
        'api_id'          => 9,
        'code'            => 'test-two-keys',
        'display_name'    => 'API With Two Keys',
        'endpoint'        => 'localhost',
        'default_path'    => '/two-keys',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithTwoPendingKeys'  => array(
        'api_id'          => 10,
        'code'            => 'test-two-pending',
        'display_name'    => 'API With Two Pending Keys',
        'endpoint'        => 'localhost',
        'default_path'    => '/two-pending',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithoutOwner'  => array(
        'api_id'          => 11,
        'code'            => 'test-without-owner',
        'display_name'    => 'API Without An Owner',
        'endpoint'        => 'localhost',
        'default_path'    => '/11',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiOwnedByUser18'  => array(
        'api_id'          => 12,
        'code'            => 'test-owned-by-user18',
        'display_name'    => 'API Owned By User 18',
        'endpoint'        => 'localhost',
        'default_path'    => '/12',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_AUTO,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'owner_id'        => 18,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithZeroKeys'  => array(
        'api_id'          => 13,
        'code'            => 'test-zero-keys',
        'display_name'    => 'API With Zero Keys',
        'endpoint'        => 'localhost',
        'default_path'    => '/zero-keys',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithZeroPendingKeys'  => array(
        'api_id'          => 14,
        'code'            => 'test-zero-pending-keys',
        'display_name'    => 'API With Zero Pending Keys',
        'endpoint'        => 'localhost',
        'default_path'    => '/zero-pending-keys',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithDefaultPath' => array(
        'api_id'          => 15,
        'code'            => 'test-with-default-path',
        'display_name'    => 'API with a default_path',
        'endpoint'        => 'local',
        'default_path'    => '/withDefaultPath',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiWithoutDefaultPath' => array(
        'api_id'          => 16,
        'code'            => 'test-without-default-path',
        'display_name'    => 'API without a default_path',
        'endpoint'        => 'local',
        'queries_second'  => 10,
        'queries_day'     => 1000,
        'endpoint_timeout'=> 10,
        'approval_type'   => Api::APPROVAL_TYPE_OWNER,
        'protocol'        => Api::PROTOCOL_HTTP,
        'strict_ssl'      => Api::STRICT_SSL_FALSE,
        'visibility'      => Api::VISIBILITY_PUBLIC,
        'created'         => '2016-06-07 15:08:12',
        'updated'         => '2016-06-07 15:08:12',
    ),
    'apiVisibleByInvitationOnly' => array(
        'api_id'           => 17,
        'code'             => 'test-visible-by-invitation-only',
        'display_name'     => 'API visible by invitation only',
        'endpoint'         => 'local',
        'default_path'     => '/visibleByInvitationOnly',
        'queries_second'   => 10,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_OWNER,
        'protocol'         => Api::PROTOCOL_HTTPS,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_INVITATION,
        'created'          => '2016-06-07 10:03:55',
        'updated'          => '2016-06-07 10:03:55',
    ),
    'apiVisibleByInvitationOnlyWithNoInvitations' => array(
        'api_id'           => 18,
        'code'             => 'test-vis-by-inv-only-no-invs',
        'display_name'     => 'API visible by invitation only with no invitations',
        'endpoint'         => 'local',
        'default_path'     => '/visibleByInvitationOnlyWithNoInvitations',
        'queries_second'   => 10,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_OWNER,
        'protocol'         => Api::PROTOCOL_HTTPS,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_INVITATION,
        'created'          => '2016-06-07 15:47:59',
        'updated'          => '2016-06-07 15:47:59',
    ),
    'publicApiThatRequiresApproval' => array(
        'api_id'           => 19,
        'code'             => 'test-pub-that-requires-approval',
        'display_name'     => 'Public API that requires approval',
        'endpoint'         => 'local',
        'default_path'     => '/publicApiThatRequiresApproval',
        'queries_second'   => 10,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_OWNER,
        'protocol'         => Api::PROTOCOL_HTTPS,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_PUBLIC,
        'created'          => '2016-06-14 14:11:00',
        'updated'          => '2016-06-14 14:11:00',
    ),
    'publicApiThatAutoApprovesKeys' => array(
        'api_id'           => 20,
        'code'             => 'test-pub-that-auto-approves-keys',
        'display_name'     => 'Public API that auto-approves keys',
        'endpoint'         => 'local',
        'default_path'     => '/publicApiThatAutoApprovesKeys',
        'queries_second'   => 10,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_AUTO,
        'protocol'         => Api::PROTOCOL_HTTPS,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_PUBLIC,
        'created'          => '2016-06-14 14:11:00',
        'updated'          => '2016-06-14 14:11:00',
    ),
    'apiVisibleByInvitationOnlyWith2UserAnd1DomainInvitation' => array(
        'api_id'           => 21,
        'code'             => 'test-by-inv-only-with-2u-1d-invs',
        'display_name'     => 'API visible by invitation only with some invitations',
        'endpoint'         => 'local',
        'default_path'     => '/by-inv-only-with-2u-1d-invs',
        'queries_second'   => 10,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_OWNER,
        'protocol'         => Api::PROTOCOL_HTTPS,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_INVITATION,
        'created'          => '2016-07-20 11:35:03',
        'updated'          => '2016-07-20 11:35:03',
    ),
    'callableTestApi' => array(
        'api_id'           => 22,
        'code'             => 'test',
        'display_name'     => 'An API that can be called from unit tests.',
        'endpoint'         => 'proxy',
        'default_path'     => '/',
        'queries_second'   => 100,
        'queries_day'      => 1000,
        'endpoint_timeout' => 10,
        'approval_type'    => Api::APPROVAL_TYPE_AUTO,
        'protocol'         => Api::PROTOCOL_HTTP,
        'strict_ssl'       => Api::STRICT_SSL_TRUE,
        'owner_id'         => 17,
        'visibility'       => Api::VISIBILITY_INVITATION,
        'created'          => '2016-11-01 15:54:01',
        'updated'          => '2016-11-01 15:54:01',
    ),
    'apiThatRequiresApprovalButNotSignatures' => array(
        'api_id'            => 23,
        'code'              => 'test-req-approval-not-sig',
        'display_name'      => 'An owner-approve API that does not require signatures.',
        'endpoint'          => 'local',
        'default_path'      => '/test-req-approval-not-sig',
        'queries_second'    => 100,
        'queries_day'       => 1000,
        'endpoint_timeout'  => 10,
        'approval_type'     => Api::APPROVAL_TYPE_AUTO,
        'protocol'          => Api::PROTOCOL_HTTP,
        'strict_ssl'        => Api::STRICT_SSL_TRUE,
        'owner_id'          => 17,
        'visibility'        => Api::VISIBILITY_INVITATION,
        'require_signature' => Api::REQUIRE_SIGNATURES_NO,
        'created'           => '2016-11-08 16:13:14',
        'updated'           => '2016-11-08 16:13:14',
    ),
);
