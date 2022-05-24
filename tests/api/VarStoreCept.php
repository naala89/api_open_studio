<?php

$I = new ApiTester($scenario);
$uri = $I->getCoreBaseUri() . '/var_store';
$accVarStores = $appVarStores = [];

// Test role access to create var_store.

$I->performLogin(getenv('TESTER_CONSUMER_NAME'), getenv('TESTER_CONSUMER_PASS'));

$I->wantTo('Test a consumer cannot create a var for an account they are assigned to');
$I->sendPost($uri, ['accid' => 2, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test a consumer cannot create a var for an application they are assigned to');
$I->sendPost($uri, ['appid' => 2, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test a consumer cannot create a var for an account they are not assigned to');
$I->sendPost($uri, ['accid' => 1, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test a consumer cannot create a var for an application they are not assigned to');
$I->sendPost($uri, ['appid' => 1, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->performLogin(getenv('TESTER_DEVELOPER_NAME'), getenv('TESTER_DEVELOPER_PASS'));

$I->wantTo('Test a developer cannot create a var for an account they are assigned to');
$I->sendPost($uri, ['accid' => 2, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test a developer can create a var for an application they are assigned to');
$I->sendPost($uri, ['appid' => 2, 'key' => 'varkey1', 'val' => 'varval1']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'null',
        'appid' => 'integer:>1:<3',
        'key' => 'string:regex(~varkey1~)',
        'val' => 'string:regex(~varval1~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$appVarStores[2][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test a developer cannot create a var for an account they are not assigned to');
$I->sendPost($uri, ['accid' => 1, 'key' => 'varkey2', 'val' => 'varval2']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test a developer cannot create a var for an application they are not assigned to');
$I->sendPost($uri, ['appid' => 1, 'key' => 'varkey2', 'val' => 'varval2']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->performLogin(getenv('TESTER_APPLICATION_MANAGER_NAME'), getenv('TESTER_APPLICATION_MANAGER_PASS'));

$I->wantTo('Test an application manager cannot create a var for an account they are assigned to');
$I->sendPost($uri, ['accid' => 2, 'key' => 'varkey2', 'val' => 'varval2']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test an application manager can create a var for an application they are assigned to');
$I->sendPost($uri, ['appid' => 2, 'key' => 'varkey2', 'val' => 'varval2']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'null',
        'appid' => 'integer:>1:<3',
        'key' => 'string:regex(~varkey2~)',
        'val' => 'string:regex(~varval2~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$appVarStores[2][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an application manager cannot create a var for an account they are not assigned to');
$I->sendPost($uri, ['accid' => 1, 'key' => 'varkey3', 'val' => 'varval3']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test an application manager cannot create a var for an application they are not assigned to');
$I->sendPost($uri, ['appid' => 1, 'key' => 'varkey3', 'val' => 'varval3']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->performLogin(getenv('TESTER_ACCOUNT_MANAGER_NAME'), getenv('TESTER_ACCOUNT_MANAGER_PASS'));

$I->wantTo('Test an account manager can create a var for an account they are assigned to');
$I->sendPost($uri, ['accid' => 2, 'key' => 'varkey3', 'val' => 'varval3']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'integer:>1:<3',
        'appid' => 'null',
        'key' => 'string:regex(~varkey3~)',
        'val' => 'string:regex(~varval3~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$accVarStores[2][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an account manager can create a var for an application belonging to the account they are assigned to');
$I->sendPost($uri, ['appid' => 2, 'key' => 'varkey4', 'val' => 'varval4']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'null',
        'appid' => 'integer:>1:<3',
        'key' => 'string:regex(~varkey4~)',
        'val' => 'string:regex(~varval4~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$appVarStores[2][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an account manager cannot create a var for an account they are not assigned to');
$I->sendPost($uri, ['accid' => 1, 'key' => 'varkey5', 'val' => 'varval5']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->wantTo('Test an account manager cannot create a var for an application they are not assigned to');
$I->sendPost($uri, ['appid' => 1, 'key' => 'varkey5', 'val' => 'varval5']);
$I->seeResponseCodeIs(403);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'result' => 'error',
    'data' => [
        'id' => 'var store create process',
        'code' => 4,
        'message' => 'Permission denied.'
    ]
]);

$I->performLogin(getenv('TESTER_ADMINISTRATOR_NAME'), getenv('TESTER_ADMINISTRATOR_PASS'));

$I->wantTo('Test an administrator can create a var for the apiopenstudio account');
$I->sendPost($uri, ['accid' => 1, 'key' => 'varkey5', 'val' => 'varval5']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'integer:>0:<2',
        'appid' => 'null',
        'key' => 'string:regex(~varkey5~)',
        'val' => 'string:regex(~varval5~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$accVarStores[1][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an administrator can create a var for the test account');
$I->sendPost($uri, ['accid' => 2, 'key' => 'varkey6', 'val' => 'varval6']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'integer:>1:<3',
        'appid' => 'null',
        'key' => 'string:regex(~varkey6~)',
        'val' => 'string:regex(~varval6~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$accVarStores[2][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an administrator can create a var for the core application');
$I->sendPost($uri, ['appid' => 1, 'key' => 'varkey7', 'val' => 'varval7']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'null',
        'appid' => 'integer:>0:<2',
        'key' => 'string:regex(~varkey7~)',
        'val' => 'string:regex(~varval7~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$appVarStores[1][$response['data']['key']] = $response['data']['vid'];

$I->wantTo('Test an administrator can create a var for the test application');
$I->sendPost($uri, ['appid' => 2, 'key' => 'varkey8', 'val' => 'varval8']);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseMatchesJsonType([
    'result' => 'string:regex(~ok~)',
    'data' => [
        'vid' => 'integer:>0',
        'accid' => 'null',
        'appid' => 'integer:>1:<3',
        'key' => 'string:regex(~varkey8~)',
        'val' => 'string:regex(~varval8~)',
    ],
]);
$response = json_decode($I->getResponse(), true);
$appVarStores[2][$response['data']['key']] = $response['data']['vid'];




//
//
//// Test role access to read var_store.
//
//$yamlFilename = 'varStoreRead.yaml';
//
//$I->performLogin(getenv('TESTER_DEVELOPER_NAME'), getenv('TESTER_DEVELOPER_PASS'));
//$I->createResourceFromYaml($yamlFilename);
//$I->deleteHeader('Authorization');
//
//$uri = $I->getMyBaseUri() . '/testing_var_store';
//
//$I->performLogin(getenv('TESTER_CONSUMER_NAME'), getenv('TESTER_CONSUMER_PASS'));
//
//// phpcs:ignore
//$I->wantTo('Test a consumer can read a var within an application they are assigned to with validate_access set to true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => true]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test a consumer can read a var within an application they are assigned to with validate_access set to false, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test a consumer can read a var within an application they are NOT assigned to with validate_access as default true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey5']]);
//$I->seeResponseCodeIs(400);
//$I->seeResponseIsJson();
//$I->seeResponseContainsJson([]);
//
//// phpcs:ignore
//$I->wantTo('Test a consumer can read a var within an application they are NOT assigned to with validate_access set to false, by vid ');
//$I->sendGet($uri, ['vid' => $varStores['varkey5'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//$I->performLogin(getenv('TESTER_DEVELOPER_NAME'), getenv('TESTER_DEVELOPER_PASS'));
//
//// phpcs:ignore
//$I->wantTo('Test a developer can read a var within an application they are assigned to with validate_access set to true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => true]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test a developer can read a var within an application they are assigned to with validate_access set to false, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test a developer cannot read a var within an application they are NOT assigned to with validate_access as default true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey5']]);
//$I->seeResponseCodeIs(400);
//$I->seeResponseIsJson();
//$I->seeResponseContainsJson([]);
//
//// phpcs:ignore
//$I->wantTo('Test a developer can read a var within an application they are NOT assigned to with validate_access set to false, by vid ');
//$I->sendGet($uri, ['vid' => $varStores['varkey5'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//$I->performLogin(getenv('TESTER_APPLICATION_MANAGER_NAME'), getenv('TESTER_APPLICATION_MANAGER_PASS'));
//
//// phpcs:ignore
//$I->wantTo('Test an application manager can read a var within an application they are assigned to with validate_access set to true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => true]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an application manager can read a var within an application they are assigned to with validate_access set to false, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an application manager cannot read a var within an application they are NOT assigned to with validate_access as default true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey5']]);
//$I->seeResponseCodeIs(400);
//$I->seeResponseIsJson();
//$I->seeResponseContainsJson([]);
//
//// phpcs:ignore
//$I->wantTo('Test an application manager can read a var within an application they are NOT assigned to with validate_access set to false, by vid ');
//$I->sendGet($uri, ['vid' => $varStores['varkey5'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//$I->performLogin(getenv('TESTER_ACCOUNT_MANAGER_NAME'), getenv('TESTER_ACCOUNT_MANAGER_PASS'));
//
//// phpcs:ignore
//$I->wantTo('Test an account manager can read a var within an application they are assigned to with validate_access set to true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => true]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an account manager can read a var within an application they are assigned to with validate_access set to false, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an account manager cannot read a var within an application they are NOT assigned to with validate_access as default true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey5']]);
//$I->seeResponseCodeIs(400);
//$I->seeResponseIsJson();
//$I->seeResponseContainsJson([]);
//
//// phpcs:ignore
//$I->wantTo('Test an account manager can read a var within an application they are NOT assigned to with validate_access set to false, by vid ');
//$I->sendGet($uri, ['vid' => $varStores['varkey5'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//$I->performLogin(getenv('TESTER_ADMINISTRATOR_NAME'), getenv('TESTER_ADMINISTRATOR_PASS'));
//
//// phpcs:ignore
//$I->wantTo('Test an administrator can read a var within an application they are assigned to with validate_access set to true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => true]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an administrator can read a var within an application they are assigned to with validate_access set to false, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey1'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>1:<3',
//            'key' => 'string:regex(~varkey1~)',
//            'val' => 'string:regex(~varval1~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an administrator cannot read a var within an application they are NOT assigned to with validate_access as default true, by vid');
//$I->sendGet($uri, ['vid' => $varStores['varkey5']]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//// phpcs:ignore
//$I->wantTo('Test an administrator can read a var within an application they are NOT assigned to with validate_access set to false, by vid ');
//$I->sendGet($uri, ['vid' => $varStores['varkey5'], 'validate_access' => false]);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsJson();
//$I->seeResponseMatchesJsonType([
//    'result' => 'string:regex(~ok~)',
//    'data' => [
//        [
//            'vid' => 'integer:>0',
//            'appid' => 'integer:>0:<2',
//            'key' => 'string:regex(~varkey5~)',
//            'val' => 'string:regex(~varval5~)',
//        ],
//    ],
//]);
//
//// Clean up
//
//$uri = $I->getCoreBaseUri() . '/var_store';
//
//$I->performLogin(getenv('TESTER_ADMINISTRATOR_NAME'), getenv('TESTER_ADMINISTRATOR_PASS'));
//foreach ($varStores as $vid) {
//    $I->sendDelete($uri . '/' . $vid);
//}
//
//$I->performLogin(getenv('TESTER_DEVELOPER_NAME'), getenv('TESTER_DEVELOPER_PASS'));
//$I->tearDownTestFromYaml($yamlFilename);
