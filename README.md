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
bin/cxnapp http://example.localhost 'O=MyOrg'
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
define('CIVICRM_CXN_VERIFY', false);
```

Note: The above configuration is vulnerable to manipulation by
man-in-the-middle attackers.  It's acceptable for local development but
should not be used in production sites.

Now use the Cxn.register API to make a connection, e.g.

```
drush cvapi cxn.register appMetaUrl=http://example.localhost/cxn/metadata.json debug=1
```

## Development

To customize the registration process, extend RegistrationServer and
override the functions, onCxnRegister() and onCxnUnregister().

The default configuration stores shared secrets in a JSON file. This
is not safe for production environments.  You should:

 * Provide a different implementation of CxnStoreInterface.
 * Edit AdhocConfig.php to use the new CxnStore class.

## Go live

1. Deploy on a real web-server.

2. Send a copy of the "metadata.json" to your point-of-contact at
civicrm.org.

3. Your POC will provide an updated copy of metadata.json with
a signed certificate. This will be the official copy distributed
to downstream sites. You should update your copy of metadata.json
to match.

4. Update your copy of metadata.json.
