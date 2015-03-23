The CXN adhoc app is a simple demonstration.

Sites may register for the adhoc app. The app administrator may then issue
adhoc API commands.

== Tutorial

1. Generate an identity for the application:

```
bin/cxn-adhoc http://example.localhost '/O=MyOrg'
```

Take a look at app/metadata.json. This file provides all the metadata about
the application.

2. Setup a virtual host for the 'web' folder -- e.g. in Debian/Ubuntu with
civicrm-buildkit:

```
cd web
amp create --url http://example.localhost
apache2ctl restart
curl http://example.localhost/
## Note: This should output the application description.
```

3. Configure your test instance of CiviCRM to load this application.

In your local CiviCRM installation, edit civicrm.settings.php
and set:

```
define('CIVICRM_CXN_VERIFY', false);
```

Note: The above configuration is vulnerable to manipulation by
man-in-the-middle attackers.  It's acceptable for local development but
should not be used in production sites.

4. In CiviCRM, navigate to "/civicrm/a/#/cxn". In "Advanced", enter
the URL, "http://example.localhost/cxn/metadata.json".

== Development

To customize the registration process, extend RegistrationServer and
override the functions, onCxnRegister() and onCxnUnregister().

The default data-store uses a shared JSON file. This is not safe for
production environments.  You should:

 * Provide a different implementation of CxnStoreInterface.
 * Edit AdhocConfig.php to use the new CxnStore class.

== Go live

1. Deploy on a real web-server.

2. Send a copy of the "metadata.json" to your point-of-contact at
civicrm.org.

3. Your POC will provide a signed an updated copy of metadata.json
along with a signed certificate. This will be the official copy
distributed to downstream sites. You should update your copy of
metadata.json to match.

4. Update your copy of metadata.json.
