=== WooCommerce GDPR (DSGVO) Data Protection ===
Contributors: telberia
Tags: user data encryption, Verschlüsselung, Datenschutzverordnung, GDPR Regulations, DSGVO
Requires at least: 4.9.6
Tested up to: 4.9.6
Requires PHP: 5.4
Stable tag: 1.0.3

This plugin will encrypt all Woocommerce users and orders data very securely in the database. It also allowing users to manage their personal data. This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR).

== Description ==

> Note: Please DO BACKUP your site before using this plugin or updating to newer version.

ACTIVATION THIS PLUGIN DOES NOT GUARANTEE YOU FULLY COMPLY WITH GDPR. PLEASE CONTACT A GDPR CONSULTANT OR LAW FIRM TO ASSESS NECESSARY MEASURES.

On 25th May 2018, the GDPR (General Data Protection Regulation) enacted by the EU will come into effect. Is your website running on WordPress GDPR compliant? What are the steps that you must take to ensure that you follow the guidelines? What if you neglect this?

WooCommerce GDPR(DSGVO) Data Protection will help you to secure all your user's private data with the most secure AES Encryption and Decryption with OpenSSL method. This plugin will encrypt all Woocommerce users and orders data very securely in the database. It also allowing users to manage their personal data. This plugin assists website and webshop owners to comply with European privacy regulations (known as GDPR).

> Note: Please BACKUP your site before using.

== FEATURES ==
Easily integrate a Personal Data Request Form for your visitors/users in front-end, with two options:
– Personal Data Export
– Personal Data Erasure

= Data Request Workflow: =

* The user/visitor use your Personal Data Request Form to ask for Personal Data Export or Erasure.
* A request is created in WordPress Admin > Settings > Privacy.
* An email is sent to the user/visitor to confirm this request.
* The user request is set to Confirmed in WordPress Admin > Settings > Privacy.
* An email is sent to the website administrator to validate the request.
* The personal data are sent by email to the user/visitor (as a 3-day available download link), or erased, depending on the user request type.

With the WooCommerce GDPR(DSGVO) Data Protection it is possible to automatically add a GDPR checkbox to WordPress Registration, WooCommerce and WordPress Comments. By ticking this checkbox your visitors and customers explicitly allow you to handle their personal data for a defined purpose (i.e. taking care of their order).

We are a European Company. To hire our agency to help you with this plugin installation or other requirements please contact us through our site **[contact form](http://www.codemenschen.at/contact-us/) or email [office@telberia.com](mailto:office@telberia.com "Telberia e.U") directly.

= How does this plugin work? =
This plugin will encrypt all users data very securely in the database. When any user login to their account, all his data is decrypted in the database unless they log out. When the log out all their data again encrypted and saves in the database. So each time User login all data gets decrypted and after logout again encrypted. So its perfect also working with Other Plugins. This plugin will also work with new user registration.

= How can I see all users data in normal form? =
There is a page appear in admin menu called **'Encrypted Users'** and in this page, an input field will show where you have to insert a secret key which will be provided after plugin installation. Then you can see all users in decrypted form.

* You can encrypt all your wordpress users without any limit
* 
For more information about the **WooCommerce GDPR(DSGVO) Data Protection**, visit the official page on **[codemenschen.at](http://www.codemenschen.at/woocommerce-gdpr-dsgvo-data-protection/ "WooCommerce GDPR(DSGVO) Data Protection")**

* 6 month support & updates included for premium plugin customers

**Buy Premium Plugin from [here](http://www.codemenschen.at/downloads/woocommerce-gdpr-dsgvo-data-protection/ "WooCommerce GDPR(DSGVO) Data Protection Plugin")**

== Installation ==
1. Unzip the downloaded zip file.
2. Upload the plugin folder into the wp-content/plugins/ directory of your WordPress site.
3. Activate the plugin through the ‘Plugins’ screen in WordPress.
4. Login after activation.
5. Go to 'WooCoomerce GDPR' page which appears in admin users menu.
6. Copy and keep this key secret because this is used for decrypt the data.

== Frequently Asked Questions ==

= What is GDPR?
GDPR stands for General Data Protection Regulation and it is a new data protection law in the EU, which comes into force in May 2018.

The aim of the GDPR is to give citizens of the EU control over their personal data, and change the approach of organizations across the world towards data privacy.

The GDPR provides much stronger rules than existing laws and is much more restrictive than the “EU cookie law.”

For instance, users must confirm that their data can be collected, there must a clear privacy policy showing what data is going to be stored, how it is going to be used, and provide the user a right to withdraw the consent to the use of personal data (consequently deleting the data), if required.

The GDPR law applies to data collected about EU citizens from anywhere in the world. As a consequence, a website with any EU visitors or customers must comply with the GDPR, which means that virtually all websites and businesses must comply.

To better understand the regulation, take a look at the publication of the regulations in the Official Journal of the European Union, which defines all terms related to the law. There are two main aspects of the GDPR: “personal data” and “processing of personal data.” Here’s how it relates to running a WordPress site:

personal data pertains to “any information relating to an identified or identifiable natural person” – like name, email, address or even an IP address,
whereas processing of personal data refers to “any operation or set of operations which is performed on personal data”. Therefore, a simple operation of storing an IP address on your web server logs constitutes processing of personal data of a user.

= How does this plugin work?
This plugin will encrypt all users data very securely in the database. When any user login to their account, all his data is decrypted in the database unless they log out. When the log out all their data again encrypted and saves in the database. So each time User login all data gets decrypted and after logout again encrypted. So its perfect also working with Other Plugins.

= How can I see all users data in normal form?
There is a page appear in WooCommerce GDPR submenu called **'Encrypted Users'** and in this page, an input field will show where you have to insert a secret key which will be provided after plugin installation. Then you can see all users in decrypted form.

= Where can I find the secret key?
Just after successfully plugin installation there is a page appear in admin submenu called **'Settings'** under the WooCommerce GDPR. Where you can see the secret key also you can send this key to your email. Remember that this secret key block is active for only one time and when you sent the secret key to the email address this block will automatically remove from the settings page. So that anyone can not see the secret key again. So please keep this key safe so you can decrypt user's data later.

= Can I decrypt all user data permanently?
Yes, you can decrypt all users data permanently. For doing this there is a page in WooCommerce GDPR submenu called **'Decrypt All User Data'**. But after this WP GDPR Data Protection plugin will deactivate automatically.

= What fields will encrypt this plugin
This plugin will encrypt following fields in wordpress database tables:

Only for Wordpress
* Wordpres users table
** user_login
** user_pass
** user_nicename
** user_email
** user_url
** display_name
* Wordpress usermeta table
** nickname
** first_name
** last_name
** description

If Woocommere Enable
* Wordpress usermeta table
** billing_first_name
** billing_last_name
** billing_company
** billing_address_1
** billing_address_2
** billing_city
** billing_postcode
** billing_country
** billing_state
** billing_phone
** billing_email
** shipping_first_name
** shipping_last_name
** shipping_company
** shipping_address_1
** shipping_address_2
** shipping_city
** shipping_postcode
** shipping_country
** shipping_state

= Can I request any new feature?
Yes, you can email a request to office@telberia.com. We always appreciate your suggestions.

== Suggestions ==

We constantly update this plugin to take care of more GDPR related issues. If you have suggestions about how to improve WooCommerce GDPR(DSGVO) Data Protection, you can [write us](mailto:office@telberia.com "Telberia e.U").

== Screenshots ==
1. Settings Page
2. Normal User Database
3. Normal User Data in Admin
4. Encrypted User Database
5. Encrypted User Data in Admin
6. User Request Form
7. Data Export Requests
8. Decrypt All Users

== Changelog ==

= Version 1.0.3 - Released: May 25, 2018 =

* New: Function for Including GDPR & Privacy Policies to checkbox label
* New: Added ‘WooCommerce’ integration
* New: Added ‘WordPress Registration’ integration
* New: Added ‘WordPress Comments’ integration
* Added screenshots
* Added textual changes
* Added default error message
* Upgrading Some styles
* Fixing some bugs

= Version 1.0.2 - Released: April 14, 2018 =

* update wp-gdpr-data-protection.php
* update core.php
* update safeCrypto.php

= Version 1.0.1 - Released: April 13, 2018 =

* update wp-gdpr-data-protection.php
* update core.php
* update safeCrypto.php
* update decryptUserData.php

= Version 1.0.0 - Released: April 03, 2018 =

* Initial release