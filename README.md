
# Reorganized

This repo is preserved for historical purposes. It includes a deprecated reference-implementation based on Silex. The current RI is based on Symfony 2 Standard Edition. See https://github.com/civicrm/cxnapp/

---

The CXN adhoc app is a simple demonstration.

Sites may register for the adhoc app. The app administrator may then issue
adhoc API commands.

## Tutorial

#### Get the code

```
git clone https://github.com/totten/cxnapp
cd cxnapp
composer install
```

#### Generate an identity for the application:

```
bin/cxnapp init http://example.localhost 'O=MyOrg'
```

The first argument is the URL where the app will be accessible. The second
argument is a "DN" (as in X.509 or LDAP) for your organization.

Take a look at app/metadata.json. This file provides all the metadata about
the application.

#### Setup a virtual host for the 'web' folder

e.g. in Debian/Ubuntu with civicrm-buildkit:

```
cd web
amp create --url http://example.localhost
apache2ctl restart
curl http://example.localhost/
## Note: This should output the application description.
```

#### Connect a test instance of CiviCRM

In your local CiviCRM installation, edit civicrm.settings.php
and set:

```
define('CIVICRM_CXN_CA', 'none');
define('CIVICRM_CXN_APPS_URL', 'http://example.localhost/cxn/apps');
```

(Note: The above configuration is vulnerable to man-in-the-middle attacks.
It's acceptable for local development but should not be used in production
sites.  Consequently, there is no API for reading or writing these
settings.)

You can now connect using the CiviCRM UI (/civicrm/a/#/cxn). Alternatively,
you can register on the command-line:

```
## Register via URL
drush cvapi cxn.register app_meta_url=http://example.localhost/cxn/metadata.json debug=1

## Register via app ID
drush cvapi cxn.register app_guid=app:abcd1234abcd1234 debug=1
```

#### Ping the test instance of CiviCRM

The cxnapp will now be able to send requests to the registered instance of Civi. For example,
we can use the System.get API to determine the active version of Civi:

```
$ bin/cxnapp get
+--------------------------------------+-------------------------------------------------------+
| Link ID                              | Site URL                                              |
+--------------------------------------+-------------------------------------------------------+
| cxn:6bf52a5773fc8bbba8cc5befc85b7589 | http://d46.l/sites/all/modules/civicrm/extern/cxn.php |
+--------------------------------------+-------------------------------------------------------+

$ bin/cxnapp call cxn:6bf52a5773fc8bbba8cc5befc85b7589 system.get
CxnID: cxn:6bf52a5773fc8bbba8cc5befc85b7589
Site URL: http://d46.l/sites/all/modules/civicrm/extern/cxn.php
Entity: system
Action: get
Params: Array
(
    [version] => 3
)

Result: Array
(
    [is_error] => 0
    [version] => 3
    [count] => 1
    [id] => 0
    [values] => Array
        (
            [0] => Array
                (
                    [version] => 4.6.0
                    [uf] => Drupal
                )

        )

)
```

## Development

To customize the registration process, extend RegistrationServer and
override the functions, onCxnRegister() and onCxnUnregister().

The default configuration stores shared secrets in a JSON file. This
is not safe for production environments.  You should:

 * Provide a different implementation of CxnStoreInterface.
 * Edit AdhocConfig.php to use the new CxnStore class.

## From development to production

One may deploy instances of cxnapp to development, staging and production
using essentially the same procedure -- download the code, configure the web
server, and run "cxnapp init" to produce an appId and keypair.  However, as
you progress, the certification requirements become more stringent.

Here are a few deployment recipes:

 * Local development
   * Deploy your app on localhost (e.g. "http://example.localhost").
   * Don't bother with certificates.
   * In civicrm.settings.php, set ```define('CIVICRM_CXN_CA', 'none');```
   * To connect, run ```drush cvapi cxn.register app_meta_url=http://example.localhost/cxn/metadata.json debug=1```
 * Staging or private beta, unsigned / self-managed / insecure
   * Deploy your app on a public web server (e.g. "http://app.example.net").
   * In civicrm.settings.php, set ```define('CIVICRM_CXN_CA', 'none');```
   * To connect, run ```drush cvapi cxn.register app_meta_url=http://app.example.net/cxn/metadata.json debug=1```
 * Staging or private beta, signed by civicrm.org
   * Deploy your app on a public web server (e.g. "http://app.example.net").
   * Send "app/local/*.req" and the URL for metadata.json to your point-of-contact at civicrm.org.
   * Receive an updated "app/local/*.crt" and metadata.json with a certificate signed by CiviTestRootCA.
   * Deploy the updated "app/local/*.crt" and metadata.json. (This is not strictly necessary but is good for consistency.)
   * In civicrm.settings.php, set ```define('CIVICRM_CXN_CA', 'CiviTestRootCA');```
   * To connect, run ```drush cvapi cxn.register app_meta_url=http://app.example.net/cxn/metadata.json debug=1```
 * Production, signed by civicrm.org
   * Deploy your app on a public web server (e.g. "http://app.example.net").
   * Send "app/local/*.req" and the URL for metadata.json to your point-of-contact at civicrm.org.
   * Receive an updated "app/local/*.crt" and metadata.json with a certificate signed by CiviRootCA.
   * Deploy the updated "app/local/*.crt" and metadata.json. (This is not strictly necessary but is good for consistency.)
   * In civicrm.settings.php, let CIVICRM_CXN_CA use the default value (CiviRootCA).
   * To connect, use the UI.

(Aside: The processes for staging or private beta are a little more onerous
that I'd like.  It would take a day's work to improve this.)
